<?php

namespace Yosimitso\WorkingForumBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\InvalidArgumentException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;
use Yosimitso\WorkingForumBundle\Form\SearchType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Yosimitso\WorkingForumBundle\Entity\{Subforum, Thread, Post};

/**
 * Class SearchController
 *
 * @package Yosimitso\WorkingForumBundle\Controller
 */
class SearchController extends BaseController
{
    protected static $resultTypes = [
        1 => ['entity' => Thread::class,
            'view' => '@YosimitsoWorkingForum/Search/result_thread.html.twig'],
        2 => ['entity' => Post::class,
            'view' => '@YosimitsoWorkingForum/Search/result_post.html.twig']
    ];

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Breadcrumbs
     */
    protected $breadcrumbs;

    /**
     * SearchController constructor.
     * @param EntityManagerInterface $entityManager
     * @param Breadcrumbs $breadcrumbs
     */
    public function __construct(EntityManagerInterface $entityManager, Breadcrumbs $breadcrumbs)
    {
        $this->entityManager = $entityManager;

        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * @param Request $request
     *
     * @Route("/search", name="search")
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $this->breadcrumbs->addRouteItem('Forum', 'workingforum_index')
            ->addItem('forum.search_forum');

        $form = $this->createForm(SearchType::class, ['target' => 1], [
            'method' => 'GET',
            'csrf_protection' => false]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                return $this->renderResult($form, $request->query->get('page', 1));
            } catch (InvalidArgumentException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }

        }

        return $this->render('@YosimitsoWorkingForum/Search/search.html.twig', [
            'form' => $form->createView()
        ]);
    }


    protected function renderResult($form, $currentPage)
    {
        $data = $form->getData();

        if (empty(self::$resultTypes[$data['target']])) {
            throw new InvalidArgumentException('Invalid result type');
        }

        $resultType = self::$resultTypes[$data['target']];

        $paginator = $this->getPaginator($this->entityManager
            ->getRepository($resultType['entity']), $data);

        $paginator->setCurrentPage($currentPage);

        $count = $paginator->getNbResults();

        if ($paginator->getNbResults() === 0) {
            return $this->render('@YosimitsoWorkingForum/Search/result_empty.html.twig', [
                'form' => $form->createView(),
                'keywords' => $data['keywords']
            ]);
        }

        return $this->render($resultType['view'], [
            'form' => $form->createView(),
            'keywords' => $data['keywords'],
            'resultsPager' => $paginator
        ]);
    }


    protected function getPaginator($repository, $data)
    {
        $keywords = array_filter(array_map('trim', explode(' ', $data['keywords'])));

        $paginator = new Pagerfanta(new DoctrineORMAdapter($repository->search($keywords,
            $this->getAllowedSubforumList($data['forum']))));
        $paginator->setMaxPerPage($this->getParameter('yosimitso_working_forum.thread_per_page'));

        return $paginator;
    }

    protected function getAllowedSubforumList(ArrayCollection $subforums)
    {
        if (!$this->getParameter('yosimitso_working_forum.allow_anonymous_read')) {
            $subforums = $subforums->filter(function(Subforum $subforum) {
                return $this->isGranted($subforum->getAllowedRoles());
            });
        }

        return $subforums->map(function(Subforum $subforum) {
            return $subforum->getId();
        })->toArray();
    }
}