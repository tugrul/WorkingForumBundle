<?php


namespace Yosimitso\WorkingForumBundle\Event\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Yosimitso\WorkingForumBundle\Entity\Thread;
use Yosimitso\WorkingForumBundle\Entity\Post;

class ThreadAutolockEvent
{
    /**
     * @var int
     */
    protected $lockThreadOlderThan;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct($lockThreadOlderThan, EntityManagerInterface $entityManager)
    {
        $this->lockThreadOlderThan = $lockThreadOlderThan;

        $this->entityManager = $entityManager;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        if (empty($this->lockThreadOlderThan)) {
            return;
        }

        /**
         * @var Thread
         */
        $thread = $args->getEntity();

        if (!($thread instanceof Thread) || $thread->getLocked()) {
            return;
        }

        $post = $this->entityManager->getRepository(Post::class)
            ->getLastByThread($thread);

        $diff = $post->getCreateDate()->diff(new \DateTime());

        if ($diff->days > $this->lockThreadOlderThan) {
            $thread->setLocked(true);
        }
    }
}