<?php

namespace Yosimitso\WorkingForumBundle\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Yosimitso\WorkingForumBundle\Entity\User;
use Yosimitso\WorkingForumBundle\Entity\Post;
use Yosimitso\WorkingForumBundle\Entity\Subscription as SubscriptionEntity;

class PostEvent
{
    private $floodLimit;
    private $translator;
    private $em;
    private $notificationUtil;
    
    public function __construct($floodLimit, $translator, $notificationUtil)
    {
        $this->floodLimit = $floodLimit;
        $this->translator = $translator;
        $this->notificationUtil = $notificationUtil;

    }
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->em = $args->getEntityManager();

        if (!$entity instanceof Post) {
            return;
        }

        if (!$this->isFlood($entity)) {
            return;
        }

        return;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Post) {
            return;
        }
        $this->notificationUtil->notifySubscriptions($entity);

        if ($entity->getAddSubscription()) {
            $this->addSubscription($entity);
        }
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

    public function addSubscription($entity)
    {
        $subscription = new SubscriptionEntity($entity->getThread(), $entity->getUser());

        $this->em->persist($subscription);

        $this->em->flush();
    }


}