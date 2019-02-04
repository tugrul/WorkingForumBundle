<?php

namespace Yosimitso\WorkingForumBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Forum
 *
 * @package Yosimitso\WorkingForumBundle\Entity
 *
 * @ORM\Table(name="workingforum_forum")
 * @ORM\Entity()
 */
class Forum implements SlugableInterface
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
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="forum.not_blank")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Yosimitso\WorkingForumBundle\Entity\Subforum",
     *     mappedBy="forum",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    private $subforums;

    /**
     * Forum constructor.
     */
    public function __construct()
    {
        $this->subforums = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return Forum
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * Set slug
     *
     * @param string $slug
     *
     * @return Forum
     */
    public function setSlug(?string $slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getSlugProvider(): string
    {
        return $this->getName();
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
     * @return ArrayCollection
     */
    public function getSubforums()
    {
        return $this->subforums;
    }

    /**
     * @param Subforum $subforum
     *
     * @return Forum
     */
    public function addSubforum(Subforum $subforum)
    {
        if (!$this->subforums->contains($subforum)) {
            $this->subforums[] = $subforum;
            $subforum->setForum($this);
        }

        return $this;
    }

    /**
     * @param Subforum $subforum
     *
     * @return Forum
     */
    public function removeSubforum(Subforum $subforum)
    {
        if ($this->subforums->contains($subforum)) {
            $this->subforums->removeElement($subforum);
            if ($subforum->getForum() === $this) {
                $subforum->setForum(null);
            }
        }

        return $this;
    }




}
