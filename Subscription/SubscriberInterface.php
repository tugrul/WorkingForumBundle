<?php


namespace Yosimitso\WorkingForumBundle\Subscription;

use Yosimitso\WorkingForumBundle\Entity\Post;
use Yosimitso\WorkingForumBundle\Entity\Subscription;

interface SubscriberInterface
{
    public function notify(Subscription $subscription, Post $post);
}