<?php

namespace Yosimitso\WorkingForumBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Yosimitso\WorkingForumBundle\Entity\Post;
use Yosimitso\WorkingForumBundle\Entity\PostReportReview;
use Yosimitso\WorkingForumBundle\Entity\PostVote;
use Yosimitso\WorkingForumBundle\Entity\Subforum;
use Yosimitso\WorkingForumBundle\Entity\PostReport;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class ThreadController
 *
 * @Route("/post")
 *
 * @package Yosimitso\WorkingForumBundle\Controller
 */
class PostController extends BaseController
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
         $this->entityManager = $entityManager;
    }

    /**
     * vote for a post
     *
     * @Route("/{postId}/vote-up", name="vote_up", requirements={"postId": "\d+"}, options={"expose": true})
     * @IsGranted("ROLE_USER")
     *
     * @param $postId
     *
     * @return Response
     */
    public function voteUpAction($postId)
    {
        $entityManager = $this->entityManager;

        $post = $this->getPostById($postId);

        $user = $this->getUser();

        if ($post->getUser()->getId() === $user->getId()) {
            return $this->json(['success' => false,
                'message' => 'An user can\'t vote for his post']);
        }

        if ($post->getReviews()->count() > 0 || $post->getThread()->getLocked() ) {
            return $this->json(['success' => false,
                'message' => 'You can\'t vote for this post']);
        }

        $subforum = $entityManager->getRepository(Subforum::class)->findOneById(
            $post->getThread()->getSubforum()->getId()
        );

        if (is_null($subforum)) {
            return $this->json(['success' => false,
                'message' => 'Subforum not exists']);
        }

        $this->denyAccessUnlessGranted($subforum->getAllowedRoles());

        $alreadyVoted = $entityManager->getRepository(PostVote::class)
            ->findOneBy(['user' => $user, 'post' => $post]);

        if (!empty($alreadyVoted)) {
            return $this->json(['success' => false,
                'message' => 'Already voted']);
        }

        $postVote = new PostVote();
        $postVote->setPost($post)
            ->setUser($user)
            ->setVoteType(PostVote::VOTE_UP)
            ->setThread($post->getThread());

        $entityManager->persist($postVote);
        $entityManager->flush();


        return $this->json([
            'success' => true,
            'state' => [
                'data' => [
                    'route' => 'workingforum_vote_down'
                ]
            ],
            'voteCount' => $entityManager->getRepository(PostVote::class)
                ->count(['post' => $post])]);
    }

    /**
     * @Route("/{postId}/vote-down", name="vote_down", requirements={"postId": "\d+"}, options={"expose": true})
     * @IsGranted("ROLE_USER")
     *
     * @param $postId
     *
     * @return Response
     */
    public function voteDownAction($postId)
    {
        $entityManager = $this->entityManager;

        $post = $this->getPostById($postId);

        $user = $this->getUser();

        $postVoteRepository = $entityManager->getRepository(PostVote::class);

        $postVote = $postVoteRepository->findOneBy([
            'post' => $post,
            'user' => $user
        ]);

        if (empty($postVote)) {
            return $this->json([
                'success' => false,
                'message' => 'You don\'t have a vote for the post'
            ]);
        }

        $entityManager->remove($postVote);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'state' => [
                'data' => [
                    'route' => 'workingforum_vote_up'
                ]
            ],
            'voteCount' => $postVoteRepository->count(['post' => $post])
        ]);
    }

    /**
     * A user report a thread
     *
     * @Route("/{postId}/report", name="report_post", requirements={"postId": "\d+"}, options={"expose": true})
     * @IsGranted("ROLE_USER")
     *
     * @param $postId
     *
     * @return Response
     */
    function reportAction($postId)
    {
        $entityManager = $this->entityManager;

        $post = $this->getPostById($postId);

        if (empty($post)) {
            return $this->json([
                'success' => false,
                'message' => 'post_not_exists'
            ]);
        }

        if ($post->getReviews()->count() > 0) {
            return $this->json([
                'success' => false,
                'message' => 'post_moderated_before'
            ]);
        }

        $user = $this->getUser();

        $postReport = $entityManager->getRepository(PostReport::class)
            ->findOneBy(['user' => $user, 'post' => $post]);

        // already warned but that's ok, thanks anyway
        if (!empty($postReport)) {
            return $this->json([
                'success' => false,
                'message' => 'post_already_reported_by_you'
            ]);
        }


        // the post hasn't been reported and not already moderated
        $report = new PostReport();
        $report->setPost($post)->setUser($user);

        $entityManager->persist($report);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'your_report_saved'
        ]);
    }

    /**
     * @Route("/{postId}/moderate", name="moderate_post", requirements={"postId": "\d+"}, options={"expose": true})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MODERATOR')")
     *
     * @param $postId
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function moderateAction($postId, Request $request)
    {
        $post = $this->getPostById($postId);

        if (empty($post)) {
            return $this->json([
                'success' => false,
                'message' => 'post_not_exists'
            ]);
        }

        $reason = $request->request->get('reason');

        if (empty($reason)) {
            return $this->json([
                'success' => false,
                'message' => 'reason_is_empty'
            ]);
        }

        $user = $this->getUser();

        $report = new PostReport();
        $report->setPost($post)->setUser($user);

        $review = new PostReportReview();
        $review->setReport($report);
        $review->setReviewer($user);
        $review->setType(2);
        $review->setReason($reason);

        $entityManager = $this->entityManager;

        $entityManager->persist($report);
        $entityManager->persist($review);

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'your_report_saved'
        ]);
    }

    protected function getPostById($postId) : Post
    {
        $post = $this->entityManager->getRepository(Post::class)->find($postId);

        if (empty($post)) {
            throw $this->createNotFoundException('post not exists');
        }

        return $post;
    }

    /**
     * @Route("/preview", name="post_preview", options={"expose": true})
     * @IsGranted("ROLE_USER")
     */
    public function previewAction(Request $request)
    {
        return $this->render('@YosimitsoWorkingForum/Post/preview.html.twig', [
            'content' => $request->request->get('content')
        ]);
    }

}