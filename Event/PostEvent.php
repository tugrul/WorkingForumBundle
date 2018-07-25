<?php

namespace Yosimitso\WorkingForumBundle\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Yosimitso\WorkingForumBundle\Entity\User;
use Yosimitso\WorkingForumBundle\Entity\Post;
use Yosimitso\WorkingForumBundle\Entity\Subscription;

class PostEvent
{
    private $floodLimit;
    private $translator;
    private $em;
    
    public function __construct($floodLimit, $translator, $em)
    {
        $this->floodLimit = $floodLimit;
        $this->translator = $translator;
        $this->em = $em;
    }
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Post) {
            return;
        }

        if (!$this->isFlood($entity)) {
            return;
        }

        $this->notifySubscriptions($entity);

        if ($entity->getAddSubscription()) {
            $this->addSubscription($entity);
        }


        return;
    }

    private function isFlood($entity)
    {
        $dateNow = new \DateTime('now');
        $floodLimit = new \DateTime('-'.$this->floodLimit.' seconds');

        if (!is_null($entity->getUser()->getLastReplyDate()) && $floodLimit <= $entity->getUser()->getLastReplyDate()) { // USER IS FLOODING
            throw new \Exception($this->translator->trans('forum.error_flood', ['%second%' => $this->floodLimit], 'YosimitsoWorkingForumBundle'));
        }

        $entity->getUser()->setLastReplyDate($dateNow);
        return true;
    }

    private function notifySubscriptions($entity)
    {
       $notifs = $this->em->getRepository('YosimitsoWorkingForum:Subscriptions')->findByThreadId($entity->getId());
    }

    private function addSubscription($entity)
    {
        $subscription = new Subscription($entity->getThread(), $entity->getUser());

        if (!$this->em->persist($subscription)) {
            throw new \Exception('Subscription failed. Please contact an administrator');
        }
        $this->em->flush();
    }
}