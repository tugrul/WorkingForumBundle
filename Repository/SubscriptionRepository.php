<?php


namespace Yosimitso\WorkingForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Yosimitso\WorkingForumBundle\Entity\Post;


class SubscriptionRepository extends EntityRepository
{
    public function getByPost(Post $post)
    {
        return $this->createQueryBuilder('s')
            ->where('s.thread = :thread')
            ->andWhere('s.user <> :user')->setParameters([
                'thread' => $post->getThread(),
                'user' => $post->getUser()
            ])
        ->getQuery()->getResult();

    }
}