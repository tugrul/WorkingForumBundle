<?php

namespace Yosimitso\WorkingForumBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Subforum
 *
 * @package Yosimitso\WorkingForumBundle\Entity
 *
 * @ORM\Entity(repositoryClass="Yosimitso\WorkingForumBundle\Repository\SubforumRepository")
 * @ORM\Table(name="workingforum_subforum", uniqueConstraints={@ORM\UniqueConstraint(name="subforum_slug", columns={"forum_id", "slug"})})
 */
class Subforum implements SlugableInterface
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var Forum
     *
     * @ORM\ManyToOne(targetEntity="Yosimitso\WorkingForumBundle\Entity\Forum", inversedBy="subforums")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="id")
     */
    private $forum;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $slug;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Yosimitso\WorkingForumBundle\Entity\Thread",
     *     mappedBy="subforum",
     *     cascade={"remove"}
     * )
     */
    private $threads;

    /** @var array
     * @ORM\Column(name="allowed_roles", type="json")
     */

    private $allowedRoles = [];

    /**
     * Subforum constructor.
     */
    public function __construct()
    {
        $this->thread = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Subforum
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Forum
     */
    public function getForum()
    {
        return $this->forum;
    }

    /**
     * @param Forum $forum
     *
     * @return Subforum
     */
    public function setForum(?Forum $forum)
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return Subforum
     */
    public function setSlug(?string $slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlugProvider(): string
    {
        return $this->getName();
    }

    /**
     * @return ArrayCollection
     */
    public function getThreads()
    {
        return $this->thread;
    }

    /**
     * @param ArrayCollection $thread
     *
     * @return Subforum
     */
    public function setThreads(ArrayCollection $threads)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * @param Thread $thread
     *
     * @return $this
     */
    public function addThread(Thread $thread)
    {
        $this->thread[] = $thread;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedRoles()
    {
        $roles = $this->allowedRoles;

        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return $roles;
    }

    /**
     * @param ArrayCollection $allowedRoles
     *
     * @return Subforum
     */
    public function setAllowedRoles(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;

        return $this;
    }



}
