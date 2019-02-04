<?php

namespace Yosimitso\WorkingForumBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;
use Yosimitso\WorkingForumBundle\Form\SearchType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Yosimitso\WorkingForumBundle\Entity\{Forum, Subforum, Thread, Post};

/**
 * Class SearchController
 *
 * @package Yosimitso\WorkingForumBundle\Controller
 */
class SearchController extends BaseController
{
    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param PaginatorInterface $paginator
     * @param Breadcrumbs $breadcrumbs
     *
     * @Route("/search", name="search")
     *
     * @return Response
     */
    public function indexAction(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator,
                                Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->addRouteItem('Forum', 'workingforum_index')
            ->addItem('forum.search_forum');

        $listForum = $entityManager->getRepository(Forum::class)->findAll();


        $form = $this->createForm(SearchType::class, [], [
            'method' => 'GET',
            'csrf_protection' => false]);

        $form->handleRequest($request);


        $viewParams = [
            'listForum' => $listForum,
            'form' => $form->createView()
        ];

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('@YosimitsoWorkingForum/Search/search.html.twig', $viewParams);
        }

        $data = $form->getData();

        $whereSubforum = $this->getAllowedSubforumList($data['forum'])->map(function(Subforum $subforum) {
            return $subforum->getId();
        });

        $postListResult = $entityManager->getRepository(Post::class)
            ->search($data['keywords'], 0, 100, $whereSubforum->toArray());

        if (count($postListResult) === 0) {
            return $this->render('@YosimitsoWorkingForum/Search/result_empty.html.twig', array_merge($viewParams, [
                'keywords' => $data['keywords']
            ]));
        }

        $postList = $paginator->paginate(
            $postListResult,
            $request->query->get('page', 1),
            $this->getParameter('yosimitso_working_forum.thread_per_page')
        );

        return $this->render('@YosimitsoWorkingForum/Search/result.html.twig', array_merge($viewParams, [
            'postList' => $postList,
            'keywords' => $data['keywords']
        ]));
    }

    protected function getAllowedSubforumList(ArrayCollection $subforums) : ArrayCollection
    {
        if ($this->getParameter('yosimitso_working_forum.allow_anonymous_read')) {
            return $subforums;
        }

        return $subforums->filter(function(Subforum $subforum) {
            return $this->isGranted($subforum->getAllowedRoles());
        });
    }
}