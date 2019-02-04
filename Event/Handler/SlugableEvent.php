<?php

namespace Yosimitso\WorkingForumBundle\Event\Handler;

use Doctrine\ORM\Event\LifecycleEventArgs;
use EasySlugger\SeoSlugger;
use Yosimitso\WorkingForumBundle\Entity\SlugableInterface;


class SlugableEvent
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!($entity instanceof SlugableInterface)) {
            return;
        }

        $slug = $entity->getSlug();

        if (empty($slug)) {
            $entity->setSlug(SeoSlugger::slugify($entity->getSlugProvider()));
        }
    }
}