<?php

namespace Yosimitso\WorkingForumBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Yosimitso\WorkingForumBundle\DependencyInjection\Compiler\SubscriptionSubscriberPass;
use Yosimitso\WorkingForumBundle\Subscription\SubscriberInterface;

/**
 * Class YosimitsoWorkingForumBundle
 *
 * @package Yosimitso\WorkingForumBundle
 */
class YosimitsoWorkingForumBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SubscriptionSubscriberPass());
        $container->registerForAutoconfiguration(SubscriberInterface::class)
            ->addTag('workingforum.thread_post_subscriber');
    }

}
