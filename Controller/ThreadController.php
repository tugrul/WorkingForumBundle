<?php

namespace Yosimitso\WorkingForumBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

use Yosimitso\WorkingForumBundle\Entity\{Forum, Post, PostFile, PostVote,
    Subforum, Thread, PostReport, File, Subscription, UserInterface};


use Yosimitso\WorkingForumBundle\Exception\UrlChangedException;
use Yosimitso\WorkingForumBundle\Form\MoveThreadType;
use Yosimitso\WorkingForumBundle\Form\PostType;
use Yosimitso\WorkingForumBundle\Form\ThreadType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Yosimitso\WorkingForumBundle\Helper\SlugEntityManager;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Translation\TranslatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Yosimitso\WorkingForumBundle\Subscription\SubscriptionManager;

/**
 * Class ThreadController
 *
 * @Route("/thread")
 *
 * @package Yosimitso\WorkingForumBundle\Controller
 */
class ThreadController extends BaseController
{

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Breadcrumbs
     */
    protected $breadcrumbs;

    /**
     * @var SlugEntityManager
     */
    protected $slugEntityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var SubscriptionManager
     */
    protected $subscriptionManager;

    public function __construct(EntityManagerInterface $entityManager,
                                Breadcrumbs $breadcrumbs, SlugEntityManager $slugEntityManager,
                                TranslatorInterface $translator, SubscriptionManager $subscriptionManager)
    {
        $this->entityManager = $entityManager;
        $this->breadcrumbs = $breadcrumbs;
        $this->slugEntityManager = $slugEntityManager;
        $this->translator = $translator;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * Display a thread, save a post
     *
     * @param string $forumSlug
     * @param string $subforumSlug
     * @param string $threadId
     * @param string $threadSlug
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction($forumSlug, $subforumSlug, $threadId, $threadSlug,
                                Request $request, PaginatorInterface $paginator)
    {
        $entityManager = $this->entityManager;

        // TODO: log exception reasons
        try {
            /**
             * @var Forum $forum
             * @var Subforum $subforum
             * @var Thread $thread
             */
            list($forum, $subforum, $thread) = $this->slugEntityManager
                ->getThreadBySlug($forumSlug, $subforumSlug, $threadSlug, $threadId);
        } catch (EntityNotFoundException $exception) {
            $this->createNotFoundException('Not found', $exception);
        } catch (UrlChangedException $exception) {
            return $this->redirect($exception->getActualUrl());
        }

        $this->breadcrumbs
            ->addRouteItem('Forum', 'workingforum_index')
            ->addRouteItem($forum->getName(), 'workingforum_forum', ['forumSlug' => $forum->getSlug()])
            ->addRouteItem($subforum->getName(), 'workingforum_subforum', [
                'forumSlug' => $forum->getSlug(),
                'subforumSlug' => $subforum->getSlug()])
            ->addItem($thread->getLabel());

        if ($thread->getResolved()) {
            $this->addFlash('success', $this->translator->trans('forum.thread_resolved'));
        }

        $postRepository = $entityManager->getRepository(Post::class);

        $postQuery = $postRepository->getPostWithVoteCountByThread($thread);

        $postList = $paginator->paginate(
            $postQuery,
            $request->query->get('page',1),
            $this->getParameter('yosimitso_working_forum.post_per_page')
        );

        $user = $this->getUser();

        if (empty($user)) {
            return $this->render('@YosimitsoWorkingForum/Thread/thread_anonymous.html.twig', [
                'forum' => $forum,
                'subforum' => $subforum,
                'thread' => $thread,
                'postList' => $postList,
                'postRepository' => $postRepository
            ]);
        }

        $userPosts = $thread->getPosts()->filter(function (Post $post) use ($user) {
            return $post->getUser()->getId() === $user->getId();
        });

        $form = $this->createForm(PostType::class, ['subscribe' => $userPosts->count() === 0], [
            'canSubscribeThread' => $this->canSubscribeThread(),
            'canUploadFiles' => $this->canUploadFiles()]);

        $form->add('submit', SubmitType::class, [
            'label' => 'forum.submit_post'
        ]);

        $form->handleRequest($request);

        // user submit his post
        if ($form->isSubmitted()) {

            try {

                $this->denyAccessUnlessUser();

                // thread is locked cause too old according to parameters
                if ($thread->getLocked()) {
                    throw new \Exception($this->translator->trans('thread_too_old_locked',
                        [], 'YosimitsoWorkingForumBundle'));
                }

                if (!$form->isValid()) {
                    throw new \Exception( $this->translator->trans('message.posted',
                        [], 'YosimitsoWorkingForumBundle'));
                }

                $post = $this->createPost($user, $thread, $form->getData())
                    ->setIpAddress($request->getClientIp());

                $entityManager->flush();

                $this->subscriptionManager->notify($post);

                $this->addFlash('success', $this->translator->trans('message.posted', [], 'YosimitsoWorkingForumBundle'));

            } catch (AccessDeniedException $ex) {

                // $this->addFlash('danger', $this->translator->trans('message.banned', [], 'YosimitsoWorkingForumBundle'));
                $this->addFlash('danger', $ex->getMessage());
                return $this->redirectToRoute('workingforum_index');

            } catch (\Throwable $ex) {

                $this->addFlash('danger', $ex->getMessage());

            }

            $pageCount = $postList->getPageCount();

            return $this->redirectToRoute('workingforum_thread', [
                'forumSlug' => $forumSlug,
                'subforumSlug' => $subforumSlug,
                'threadSlug' => $threadSlug,
                'threadId' => $thread->getId(),
                'page' => $pageCount > 1 ? $pageCount : null
            ]);
        }

        $votedPosts = $entityManager->getRepository(PostVote::class)
            ->findBy(['user' => $user, 'thread' => $thread]);

        $votedPosts = array_map(function(PostVote $postVote){
            return $postVote->getPost()->getId();
        }, $votedPosts);

        // $parameters['fileUpload']['maxSize'] = $this->getMaxUploadSize();

        return $this->render('@YosimitsoWorkingForum/Thread/thread.html.twig', [
            'forum' => $forum,
            'subforum' => $subforum,
            'thread' => $thread,
            'postList' => $postList,
            'form' => $form->createView(),
            'votedPosts' => $votedPosts,
            'postRepository' => $postRepository,
            'subscription' => $this->getSubscription($thread, $user),
            'moveThreadForm' => $this->createForm(MoveThreadType::class, $thread, [
                'action' => $this->generateUrl('workingforum_move_thread', ['threadId' => $thread->getId()])
            ])->createView()
        ]);


    }

    protected function getSubscription(Thread $thread, UserInterface $user) : ?Subscription
    {
        return !$this->canSubscribeThread() ? null :
            $this->entityManager->getRepository(Subscription::class)
            ->findOneBy(['thread' => $thread, 'user' => $user]);
    }

    protected function canSubscribeThread()
    {
        $parameters = $this->getParameter('yosimitso_working_forum.thread_subscription');

        return !empty($parameters['enable']);
    }

    protected function canUploadFiles()
    {
        $parameters = $this->getParameter('yosimitso_working_forum.file_upload');

        return !empty($parameters['enable']);
    }

    protected function subscribeThread(Thread $thread, UserInterface $user)
    {
        if (!$this->canSubscribeThread()) {
            return;
        }

        $subscription = new Subscription();
        $subscription->setThread($thread);
        $subscription->setUser($user);

        $this->entityManager->persist($subscription);
    }

    protected function checkUserFlooding(UserInterface $user)
    {
        $floodLimit = $this->getParameter('yosimitso_working_forum.post_flood_sec');

        $limitTime = new \DateTime('-' . $floodLimit . ' seconds');

        /**
         * @var Post
         */
        $lastPost = $this->entityManager->getRepository(Post::class)
            ->getLastPostOfUser($user);

        // user is flooding
        if (!empty($lastPost) && $limitTime <= $lastPost->getCreateDate()) {
            throw new \Exception($this->translator->trans('forum.error_flood',
                ['%second%' => $floodLimit], 'YosimitsoWorkingForumBundle'));
        }
    }

    protected function createPost(UserInterface $user, Thread $thread, array $formData)
    {
        $this->checkUserFlooding($user);

        if (!empty($formData['subscribe'])) {
            $this->subscribeThread($thread, $user);
        }

        $post = new Post();
        $post->setUser($user);
        $post->setThread($thread);
        $post->setContent($formData['content']);
        $post->setPublished(true);

        $this->entityManager->persist($post);

        if (!empty($formData['files'])) {
            $this->persistUploadedFiles($post, $formData['files']);
        }

        return $post;
    }

    protected function persistUploadedFiles($post, $files)
    {
        $config = $this->getParameter('yosimitso_working_forum.file_upload');

        if (empty($config['enable'])) {
            throw new \Exception($this->translator->trans(
                'forum.file_upload.error.not_enabled',
                'YosimitsoWorkingForumBundle'
            ));
        }

        if (!empty($config['max_size_ko'])) {

            $maxSize = $config['max_size_ko'];

            $totalSize = array_reduce($files, function($carry, $item) {
                return $carry + $item->getSize() / 1000;
            }, 0);

            if ($totalSize > $maxSize) {
                throw new \Exception($this->translator->trans(
                    'forum.file_upload.error.max_size_exceeded',
                    ['%max_size%' => $maxSize],
                    'YosimitsoWorkingForumBundle'
                ));
            }

        }

        foreach ($files as $file) {

            if ($file->getError()) {
                throw new \Exception($this->translator->trans(
                    'forum.file_upload.error.default',
                    [],
                    'YosimitsoWorkingForumBundle'));
            }

            if (!in_array($file->getMimeType(), $config['accepted_format'], true)) {
                throw new \Exception($this->translator->trans(
                    'forum.file_upload.error.invalid_format',
                    ['%format%' => $file->getMimeType()],
                    'YosimitsoWorkingForumBundle'
                ));
            }

            $postFile = new PostFile();
            $postFile->setPost($post);
            $postFile->setFile($file);
            $postFile->setOriginalName($file->getClientOriginalName());
            $postFile->setExtension($file->guessExtension());
            $this->entityManager->persist($postFile);
        }

    }

    protected function getMaxUploadSize()
    {
        $config = $this->getParameter('yosimitso_working_forum.file_upload');
        return !empty($config['max_size_ko']) ? $config['max_size_ko'] : 0;
    }

    /**
     * New thread
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @param $forumSlug
     * @param $subforumSlug
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function newAction($forumSlug, $subforumSlug, Request $request)
    {
        try {
            list($forum, $subforum) = $this->slugEntityManager->getSubforumBySlug($forumSlug, $subforumSlug);
        } catch (EntityNotFoundException $exception) {
            $this->createNotFoundException('Not found', $exception);
        } catch (AccessDeniedException $exception) {

            $this->addFlash('danger', $exception->getMessage());

            return $this->redirectToRoute('workingforum_index');
        }

        $this->breadcrumbs
            ->addRouteItem('Forum', 'workingforum_index')
            ->addRouteItem($forum->getName(), 'workingforum_forum',  ['forumSlug' => $forumSlug])
            ->addRouteItem($subforum->getName(), 'workingforum_subforum', ['forumSlug' => $forumSlug,
                'subforumSlug' => $subforumSlug])
            ->addItem('forum.new_thread');



        $user = $this->getUser();

        $thread = new Thread();
        $thread->setAuthor($user);
        $thread->setSubforum($subforum);

        $form = $this->createFormBuilder(['thread' => $thread, 'post' => ['subscribe' => true]])
            ->add('thread', ThreadType::class, [
                'hasModeratorAuthorization' => $this->hasModeratorAuthorization()])
            ->add('post', PostType::class, [
                'canSubscribeThread' => $this->canSubscribeThread(),
                'canUploadFiles' => $this->canUploadFiles() ])
            ->add('submit', SubmitType::class, [
                'label' => 'forum.create_thread'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            try {

                $this->denyAccessUnlessUser();

                if (!$form->isValid()) {
                    throw new \Exception( $this->translator->trans('message.posted',
                        [], 'YosimitsoWorkingForumBundle'));
                }

                $formData = $form->getData();

                $thread = $formData['thread'];

                $post = $this->createPost($user, $thread, $formData['post'])
                    ->setIpAddress($request->getClientIp());

                $this->entityManager->persist($thread);

                $this->entityManager->flush();

                $this->subscriptionManager->notify($post);

                $this->addFlash('success',
                    $this->translator->trans('message.threadCreated', [], 'YosimitsoWorkingForumBundle')
                );

                return $this->redirectToRoute('workingforum_thread', [
                    'forumSlug' => $forum->getSlug(),
                    'subforumSlug' => $subforum->getSlug(),
                    'threadSlug' => $thread->getSlug(),
                    'threadId' => $thread->getId()
                ]);

            } catch (AccessDeniedException $ex) {

                // $this->addFlash('danger', $this->translator->trans('message.banned', [], 'YosimitsoWorkingForumBundle'));
                $this->addFlash('danger', $ex->getMessage());
                return $this->redirectToRoute('workingforum_index');

            } catch (\Throwable $ex) {

                $this->addFlash('danger', $ex->getMessage());

            }

        }

        return $this->render('@YosimitsoWorkingForum/Thread/new.html.twig',[
            'subforum'   => $subforum,
            'form'       => $form->createView(),
            'fileUploadMaxSize' => $this->getMaxUploadSize()
        ]);
    }

    /**
     * The thread is resolved
     *
     * @Route("/{threadId}/resolved", name="resolve_thread", requirements={"threadId":"\d+"}, options={"expose": true})
     *
     * @IsGranted("ROLE_USER")
     *
     * @param $threadId
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function resolveAction($threadId)
    {
        $thread = $this->getThreadById($threadId);

        // only admin, moderator or the thread's author can set a thread as resolved
        if ($this->getUser()->getId() !== $thread->getAuthor()->getId()) {
            $this->denyAccessUnlessModerator();
        }

        $thread->setResolved(true);
        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'state' => [
                'reload' => true
            ]
        ]);
    }

    /**
     * A moderator pin a thread
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MODERATOR')")
     *
     * @Route("/{threadId}/pin", name="pin_thread", requirements={"threadId":"\d+"}, options={"expose": true})
     *
     * @param $threadId
     *
     * @return RedirectResponse
     */
    public function pinAction($threadId)
    {
        $thread = $this->getThreadById($threadId);

        if ($thread->getPin()) {
            return $this->json([
                'success' => false,
                'message' => 'thread already pinned'
            ]);
        }

        $thread->setPin(true);
        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        $this->addFlash('success',
            $this->translator->trans('message.threadPinned', [], 'YosimitsoWorkingForumBundle'));

        return $this->json([
            'success' => true,
            'state' => [
                'reload' => true
            ]
        ]);
    }


    /**
     * The thread is locked by a moderator or admin
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MODERATOR')")
     *
     * @Route("/{threadId}/lock", name="lock_thread", requirements={"threadId":"\d+"}, options={"expose": true})
     *
     * @param $threadId
     *
     * @return RedirectResponse
     */
    public function lockAction($threadId)
    {
        $thread = $this->getThreadById($threadId);

        $thread->setLocked(true);
        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        $this->addFlash('success',
            $this->translator->trans('message.threadLocked', [], 'YosimitsoWorkingForumBundle'));

        return $this->json([
            'success' => true,
            'state' => [
                'reload' => true
            ]
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MODERATOR')")
     *
     * @Route("/{threadId}/move", name="move_thread", requirements={"threadId":"\d+"}, options={"expose": true})
     *
     * @param $threadId
     * @param Request $request
     *
     * @return Response
     */
    public function moveThreadAction($threadId, Request $request)
    {

        $thread = $this->getThreadById($threadId);

        $form = $this->createForm(MoveThreadType::class, $thread);

        if (!$form->isSubmitted()) {
            throw new \Exception('move thread form is not submitted');
        }

        if ($form->isValid()) {
            throw new \Exception('move thread form is not valid');
        }

        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        $this->addFlash('success', 'Thread moved');

        $subforum = $thread->getSubforum();

        return $this->redirectToRoute('workingforum_thread', [
            'forumSlug' => $subforum->getForum()->getSlug(),
            'subforumSlug' => $subforum->getSlug(),
            'threadSlug' => $thread->getSlug(),
            'threadId' => $thread->getId()
        ]);
    }

    /**
     * The thread is deleted by modo or admin
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MODERATOR')")
     * @Route("/{threadId}/delete", name="delete_thread", requirements={"threadId":"\d+"}, options={"expose": true})
     *
     * @param $threadId
     * @return RedirectResponse
     */
    public function deleteThreadAction($threadId)
    {
        if (!$this->getParameter('yosimitso_working_forum.allow_moderator_delete_thread') &&
            $this->isGranted('ROLE_MODERATOR')) {
            throw $this->createAccessDeniedException('Thread deletion is not allowed');
        }

        $thread = $this->getThreadById($threadId);

        $entityManager = $this->entityManager;

        $thread = $entityManager->getRepository(Thread::class)->findOneBySlug($threadSlug);

        $subforum = $thread->getSubforum();

        $entityManager->remove($thread);
        $entityManager->flush();

        $this->addFlash('success',
            $this->translator->trans('message.thread_deleted', [], 'YosimitsoWorkingForumBundle'));

        return $this->json([
            'success' => true,
            'state' => [
                'location' => $this->generateUrl('workingforum_subforum', [
                    'forumSlug' => $subforum->getForum()->getSlug(),
                    'subforumSlug' => $subforum->getSlug()
                ])
            ]
        ]);
    }


}

        
