<?php

namespace Yosimitso\WorkingForumBundle\Entity;

/**
 * Interface UserInterface
 *
 * @package Yosimitso\WorkingForumBundle\Entity
 */
interface UserInterface
{
    public function getId();

    public function getUsername();

    public function getAvatarUrl();

    public function setAvatarUrl($url);

    public function getEmail();

    public function getRoles();

    public function setRoles(array $roles);

    public function addRole(string $role);

    public function removeRole(string $role);
}
