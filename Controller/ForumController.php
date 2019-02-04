<?php

namespace Yosimitso\WorkingForumBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;

use phpDocumentor\Reflection\Types\This;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Node\Expression\Binary\SubBinary;


use Yosimitso\WorkingForumBundle\Entity\Post;
use Yosimitso\WorkingForumBundle\Form\RulesType;

use Yosimitso\WorkingForumBundle\Entity\Rules;
use Yosimitso\WorkingForumBundle\Entity\Forum;
use Yosimitso\WorkingForumBundle\Entity\Subforum;
use Yosimitso\WorkingForumBundle\Entity\Thread;


use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Yosimitso\WorkingForumBundle\Helper\SlugEntityManager;

use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

/**
 * Class ForumController
 *
 * @package Yosimitso\WorkingForumBundle\Controller
 */
class ForumController extends BaseController
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


    public function __construct(EntityManagerInterface $entityManager, Breadcrumbs $breadcrumbs,
                                SlugEntityManager $slugEntityManager)
    {
        $this->entityManager = $entityManager;
        $this->breadcrumbs = $breadcrumbs;
        $this->slugEntityManager = $slugEntityManager;
    }

    /**
     * Display homepage of forum with subforums
     *
     * @Route("/", name="index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $this->breadcrumbs->addItem('Forum');

        $entityManager = $this->entityManager;

        return $this->render('@YosimitsoWorkingForum/Forum/index.html.twig', [
            'forumList' => $entityManager->getRepository(Forum::class)->findAll(),
            'threadRepository' => $entityManager->getRepository(Thread::class),
            'postRepository' => $entityManager->getRepository(Post::class)
        ]);
    }

    public function forumAction($forumSlug)
    {
        try {
            $forum = $this->slugEntityManager->getForumBySlug($forumSlug);
        } catch (EntityNotFoundException $exception) {
            throw $this->createNotFoundException('Not found', $exception);
        }

        $entityManager = $this->entityManager;

        $this->breadcrumbs
            ->addRouteItem('Forum', 'workingforum_index')
            ->addItem($forum->getName());

        return $this->render('@YosimitsoWorkingForum/Forum/forum.html.twig', [
            'forum' => $forum,
            'threadRepository' => $entityManager->getRepository(Thread::class),
            'postRepository' => $entityManager->getRepository(Post::class)
        ]);
    }

    /**
     * Display the thread list of a subforum
     *
     * @param string $forumSlug
     * @param string $subforumSlug
     * @param Request $request
     * @param PaginatorInterface $paginator
     *
     * @return Response
     */
    public function subforumAction($forumSlug, $subforumSlug, Request $request, PaginatorInterface $paginator)
    {
        try {
            list($forum, $subforum) = $this->slugEntityManager
                ->getSubforumBySlug($forumSlug, $subforumSlug);
        } catch (EntityNotFoundException $exception) {
            throw $this->createNotFoundException('Not found', $exception);
        }

        $this->breadcrumbs
            ->addRouteItem('Forum', 'workingforum_index')
            ->addRouteItem($subforum->getForum()->getName(), 'workingforum_forum', ['forumSlug' => $forum->getSlug()])
            ->addItem($subforum->getName());

        $entityManager = $this->entityManager;

        $postList = $entityManager
            ->getRepository(Post::class)->getLastPostsOfThreadsBySubforum($subforum);

        $postList = $paginator->paginate(
            $postList,
            $request->query->get('page', 1),
            $this->getParameter('yosimitso_working_forum.thread_per_page')
        );


        return $this->render('@YosimitsoWorkingForum/Forum/thread_list.html.twig', [
            'subforum' => $subforum,
            'postList' => $postList,
            'postPerPage' => $this->getParameter('yosimitso_working_forum.post_per_page')
        ]);
    }

    /**
     * @Route("/rules", name="rules")
     * @Route("/rules/{locale}", name="rules_locale")
     *
     * @param string|null $locale
     *
     * @return Response
     */
    public function rulesAction($locale = null)
    {
        $this->breadcrumbs
            ->addRouteItem('Forum', 'workingforum_index')
            ->addItem('forum.forum_rules');

        $rulesRepository = $this->entityManager->getRepository(Rules::class);

        return $this->render('@YosimitsoWorkingForum/Forum/rules.html.twig', [
            'rule' => $rulesRepository->findOneBy(empty($locale) ? [] : ['lang' => $locale]),
            'rules' => $rulesRepository->findAll()
        ]);
    }
}
