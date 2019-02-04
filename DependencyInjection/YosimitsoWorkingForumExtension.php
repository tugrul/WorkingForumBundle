<?php

namespace Yosimitso\WorkingForumBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class YosimitsoWorkingForumExtension
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @package Yosimitso\WorkingForumBundle\DependencyInjection
 */
class YosimitsoWorkingForumExtension extends Extension implements PrependExtensionInterface
{

    protected static $requiredOptions = [
        'thread_per_page', 'date_format', 'allow_anonymous_read', 'theme_color', 'lock_thread_older_than',
        'vote', 'file_upload', 'post_flood_sec', 'site_title', 'thread_subscription'
    ];

    public function prepend(ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('framework.yml');
        $loader->load('twig.yml');
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach (self::$requiredOptions as $requiredOption) {
            if (!isset($config[$requiredOption])) {
                throw new \InvalidArgumentException(
                    'The "' . $requiredOption . '" option must be set in ' .
                    '"yosimitso_working_forum", please see README.MD'
                );
            }
        }

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yml');

        $container->setParameter('yosimitso_working_forum.thread_per_page', $config['thread_per_page']);
        $container->setParameter('yosimitso_working_forum.post_per_page', $config['post_per_page']);
        $container->setParameter('yosimitso_working_forum.date_format', $config['date_format']);
        $container->setParameter('yosimitso_working_forum.allow_anonymous_read', $config['allow_anonymous_read']);
        $container->setParameter('yosimitso_working_forum.allow_moderator_delete_thread', $config['allow_moderator_delete_thread']);
        $container->setParameter('yosimitso_working_forum.theme_color', $config['theme_color']);
        $container->setParameter('yosimitso_working_forum.lock_thread_older_than', $config['lock_thread_older_than']);
        $container->setParameter('yosimitso_working_forum.vote', $config['vote']);
        $container->setParameter('yosimitso_working_forum.file_upload', $config['file_upload']);
        $container->setParameter('yosimitso_working_forum.post_flood_sec', $config['post_flood_sec']);
        $container->setParameter('yosimitso_working_forum.site_title', $config['site_title']);
        $container->setParameter('yosimitso_working_forum.thread_subscription', $config['thread_subscription']);
    }
}
