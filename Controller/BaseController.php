<?php

namespace Yosimitso\WorkingForumBundle\Controller;

use Yosimitso\WorkingForumBundle\Entity\Thread;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class BaseController
 *
 * @package Yosimitso\WorkingForumBundle\Controller
 */
class BaseController extends Controller
{
    protected function isAnonymousReadAllowed()
    {
        return $this->getParameter('yosimitso_working_forum.allow_anonymous_read');
    }


    protected function hasModeratorAuthorization()
    {
        return $this->isGranted(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_MODERATOR']);
    }

    protected function denyAccessUnlessModerator()
    {
        $this->denyAccessUnlessGranted(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_MODERATOR']);
    }

    protected function denyAccessUnlessUser($allowBannedUser = false)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        !$allowBannedUser && $this->denyAccessBannedUser();
    }

    protected function denyAccessBannedUser()
    {
        $attributes = 'ROLE_USER_BANNED';

        if (!$this->isGranted($attributes)) {
            return;
        }

        $exception = $this->createAccessDeniedException('Banned user not allowed to access');
        $exception->setAttributes($attributes);

        throw $exception;
    }

    /**
     * @param $threadId
     */
    protected function getThreadById($threadId) : Thread
    {
        $thread = $this->get('doctrine.orm.default_entity_manager')
            ->getRepository(Thread::class)->find($threadId);

        if (empty($thread)) {
            throw $this->createNotFoundException('Thread not found');
        }

        return $thread;
    }
}