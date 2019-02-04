<?php

namespace Yosimitso\WorkingForumBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Constraints;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Translation\TranslatorInterface;
use Yosimitso\WorkingForumBundle\Form\AdminForumType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Yosimitso\WorkingForumBundle\Form\RulesEditType;

use Yosimitso\WorkingForumBundle\Entity\{Forum, PostReportReview, Subforum, Rules, PostReport, Post, Thread, User};


/**
 * Class AdminController
 *
 * @package Yosimitso\WorkingForumBundle\Controller
 *
 * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MODERATOR')")
 * @Route("/admin", name="admin_")
 */
class AdminController extends BaseController
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * AdminController constructor.
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     */

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }


    /**
     * @Route(name="index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MODERATOR')")
     *
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $entityManager = $this->entityManager;

        $forumList = $entityManager->getRepository(Forum::class)->findAll();

        $rulesList = $entityManager->getRepository(Rules::class)->findAll();


        $newPostReported = count(
            $entityManager->getRepository(PostReport::class)
                ->getNonReviewed()
        );

        return $this->render('@YosimitsoWorkingForum/Admin/main.html.twig', [
            'list_forum' => $forumList,
            'rulesList' => $rulesList,
            'newPostReported' => $newPostReported,
        ]);
    }

    /**
     * @Route("/forum/edit/{id}", name="forum_edit", requirements={"id": "\d+"})
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param string $id
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function editAction(string $id, Request $request)
    {
        $entityManager = $this->entityManager;

        $forum = $entityManager->getRepository(Forum::class)->find($id);

        if (empty($forum)) {
            throw $this->createNotFoundException('Forum not found by id: ' . $id);
        }

        $form = $this->createForm(AdminForumType::class, $forum);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($forum);
            $entityManager->flush();

            $this->addFlash('success', $this->translator->trans('message.saved', [],
                'YosimitsoWorkingForumBundle'));

            return $this->redirectToRoute('workingforum_admin_forum_edit', [
                'id' => $forum->getId()]);
        }

        $threadCount = $entityManager->getRepository(Thread::class)->getCountByForum($forum);
        $postCount = $entityManager->getRepository(Post::class)->getCountByForum($forum);

        return $this->render('@YosimitsoWorkingForum/Admin/Forum/form.html.twig',[
            'forum' => $forum,
            'form' => $form->createView(),
            'statistics' => [
                'thread_count' => $threadCount,
                'post_count' => $postCount,
                'thread_post_ratio' => $postCount > 0 ? ($postCount / $threadCount) : 0
            ],
        ]);
    }

    /**
     * @Route("/forum/add", name="forum_add")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function addAction(Request $request)
    {
        $forum = new Forum();
        $forum->addSubForum(new Subforum());

        $form = $this->createForm(AdminForumType::class, $forum);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($forum);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('message.saved', [],
                'YosimitsoWorkingForumBundle'));

            return $this->redirectToRoute('workingforum_admin_forum_edit', [
                'id' => $forum->getId()]);
        }

        return $this->render('@YosimitsoWorkingForum/Admin/Forum/form.html.twig',[
            'forum' => $forum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/rules/edit/{id}", name="edit_forum_rules", requirements={"id": "\d+"})
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function rulesEditAction($id, Request $request)
    {
        $rule = $this->entityManager->getRepository(Rules::class)
            ->find($id);

        if (empty($rule)) {
            throw $this->createNotFoundException('Lang not found');
        }

       return $this->handleRulesForm($rule, $request);
    }

    /**
     * @Route("/rules/add", name="new_forum_rules")
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function rulesNewAction(Request $request)
    {
        return $this->handleRulesForm(new Rules(), $request);
    }

    protected function handleRulesForm(Rules $rule, Request $request)
    {
        $form = $this->createForm(RulesEditType::class, $rule);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($rule);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('message.saved', [],
                'YosimitsoWorkingForumBundle'));

            return $this->redirectToRoute('workingforum_admin_edit_forum_rules', [
                'id' => $rule->getId()
            ]);
        }

        return $this->render('@YosimitsoWorkingForum/Admin/Rules/edit.html.twig', [
            'form' => $form->createView(),
            'parameters' => [
                'fileUpload' => ['enable' => false]
            ]
        ]);
    }

    /**
     * @Route("/report", name="report")
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MODERATOR')")
     *
     * @return Response
     */
    public function reportAction()
    {
        $postReportList = $this->entityManager->getRepository(PostReport::class)
            ->getNonReviewed();


        return $this->render('@YosimitsoWorkingForum/Admin/Report/report.html.twig',[
            'postReportList' => $postReportList,
        ]);
    }

    /**
     *
     * @Route("/report/history", name="report_history")
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MODERATOR')")
     */
    public function reportHistoryAction()
    {
        $postReportList = $this->entityManager->getRepository(PostReport::class)
            ->getReviewed();

        return $this->render('@YosimitsoWorkingForum/Admin/Report/report_history.html.twig', [
            'postReportList' => $postReportList
        ]);
    }


    /**
     *
     * @Route("/report/review/{id}", name="report_review", requirements={"id": "\d+"})
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MODERATOR')")
     *
     * @param $id
     * @param Request $request
     *
     * @return Response
     */
    public function reportReviewAction($id, Request $request, FormFactoryInterface $formFactory)
    {
        $entityManager = $this->entityManager;

        /**
         * @var PostReport
         */
        $report = $entityManager->getRepository(PostReport::class)
            ->find($id);

        if (empty($report)) {
            return $this->json([
                'success' => false,
                'message' => 'Report not exists'
            ]);
        }

        $form = $formFactory->createNamedBuilder('', FormType::class, [], ['csrf_protection' => false])
            ->add('reviewType', null, [
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Choice(['choices' => ['1', '2']])
                ]
            ])
            ->add('reason')
            ->add('banUser')
            ->getForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->json([
                'success' => false,
                'message' => 'Form not submitted'
            ]);
        }

        if (!$form->isValid()) {
            return $this->json([
                'success' => false,
                'message' => 'Form is not valid'
            ]);
        }

        $formData = $form->getData();

        $postReportReview = new PostReportReview();
        $postReportReview->setReport($report);
        $postReportReview->setReviewer($this->getUser());
        $postReportReview->setType($formData['reviewType']);

        if ($formData['reviewType'] === '2') {
            $postReportReview->setReason($formData['reason']);

            if ($formData['banUser'] === '1') {
                $entityManager->persist($report->getPost()
                    ->getUser()->addRole('ROLE_USER_BANNED'));
            }
        }

        $entityManager->persist($postReportReview);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'post review completed'
        ]);
    }

    /**
     *
     * @Route("/user", name="user")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_MODERATOR')")
     *
     * @return Response
     */
    public function userListAction()
    {
        $usersList = $this->entityManager->getRepository(User::class)->findAll();

        return $this->render('@YosimitsoWorkingForum/Admin/User/userslist.html.twig', [
            'usersList' => $usersList
        ]);

    }

    /**
     *
     * @Route("/forum/delete/{id}", name="delete_forum", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param string $forum_id
     *
     * @return Response
     */
    public function deleteForumAction(string $id)
    {
        $entityManager = $this->entityManager;

        $forum = $entityManager->getRepository(Forum::class)->find($id);

        if (!empty($forum)) {

            $entityManager->remove($forum);
            $entityManager->flush();
            $this->addFlash('success', $this->get('translator')->trans('admin.forumDeleted',
                [], 'YosimitsoWorkingForumBundle'));
        }

        return $this->redirectToRoute('workingforum_admin_index');
    }




}