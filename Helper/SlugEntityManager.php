<?php


namespace Yosimitso\WorkingForumBundle\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Yosimitso\WorkingForumBundle\Exception\UrlChangedException;

use Yosimitso\WorkingForumBundle\Entity\{Forum,Subforum,Thread};

class SlugEntityManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;


    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var ParameterBagInterface
     */
    protected $parameterBag;

    public function __construct(EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker,
                                ParameterBagInterface $parameterBag, UrlGeneratorInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->parameterBag = $parameterBag;
        $this->router = $router;
    }

    public function getForumBySlug($forumSlug)
    {
        $forum = $this->entityManager->getRepository(Forum::class)
            ->findOneBy(['slug' => $forumSlug]);

        if (empty($forum)) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(Forum::class, [$forumSlug]);
        }

        return $forum;
    }

    public function getSubforumBySlug($forumSlug, $subforumSlug)
    {
        $forum = $this->getForumBySlug($forumSlug);

        $subforum = $this->entityManager->getRepository(Subforum::class)
            ->findOneBy(['forum' => $forum, 'slug' => $subforumSlug]);

        if (empty($subforum)) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(Subforum::class, [$subforumSlug]);
        }

        $this->checkAnonymousReadAllowed($subforum->getAllowedRoles());

        return [$forum, $subforum];
    }

    public function getThreadBySlug($forumSlug, $subforumSlug, $threadSlug, $threadId)
    {
        $thread = $this->entityManager->getRepository(Thread::class)
            ->findOneBy(['id' => $threadId]);

        if (empty($thread)) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(Thread::class, [$threadId]);
        }

        $subforum = $thread->getSubforum();
        $forum = $subforum->getForum();

        $slugChanges = [];

        if ($forum->getSlug() !== $forumSlug) {
            $slugChanges[] = 'Forum slug ' . $forumSlug .
                ' not same ' . $forum->getSlug();
        }

        if ($subforum->getSlug() !== $subforumSlug) {
            $slugChanges[] = 'Subforum slug ' . $subforumSlug .
                ' not same ' . $subforum->getSlug();
        }

        if ($thread->getSlug() !== $threadSlug) {
            $slugChanges[] = 'Thread slug ' . $threadSlug .
                ' not same ' . $thread->getSlug();
        }

        if (count($slugChanges) > 0) {
            throw (new UrlChangedException(implode(PHP_EOL, $slugChanges)))
                ->setActualUrl($this->router->generate('workingforum_thread', [
                'forumSlug' => $forum->getSlug(),
                'subforumSlug' => $subforum->getSlug(),
                'threadSlug' => $thread->getSlug(),
                'threadId' => $thread->getId()
            ]));
        }

        $this->checkAnonymousReadAllowed($subforum->getAllowedRoles());

        return [$forum, $subforum, $thread];
    }

    protected function checkAnonymousReadAllowed($allowedRoles)
    {
        if (!$this->parameterBag->get('yosimitso_working_forum.allow_anonymous_read') &&
            !$this->authorizationChecker->isGranted($allowedRoles)) {

            $exception = new AccessDeniedException('Subforum access not allowed this user');
            $exception->setAttributes($allowedRoles);
            throw $exception;
        }
    }

}