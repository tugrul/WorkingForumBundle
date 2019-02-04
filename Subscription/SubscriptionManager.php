<?php


namespace Yosimitso\WorkingForumBundle\Subscription;


use Doctrine\ORM\EntityManagerInterface;

use Yosimitso\WorkingForumBundle\Entity\{Post,Subscription};

class SubscriptionManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $subscribers = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addSubscriber(SubscriberInterface $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }

    public function notify(Post $post)
    {
        if (count($this->subscribers) === 0) {
            return;
        }

        $subscriptions = $this->entityManager->getRepository(Subscription::class)
            ->getByPost($post);

        foreach ($subscriptions as $subscription) {

            foreach ($this->subscribers as $subscriber) {
                //TODO: handle exception
                $subscriber->notify($subscription, $post);
            }
        }

    }

}

