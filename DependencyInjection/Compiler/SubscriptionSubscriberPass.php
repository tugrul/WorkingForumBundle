<?php


namespace Yosimitso\WorkingForumBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Yosimitso\WorkingForumBundle\Subscription\SubscriptionManager;

class SubscriptionSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(SubscriptionManager::class)) {
            return;
        }

        $definition = $container->findDefinition(SubscriptionManager::class);

        $taggedServices = $container->findTaggedServiceIds('workingforum.thread_post_subscriber');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addSubscriber', [new Reference($id)]);
        }

    }

}