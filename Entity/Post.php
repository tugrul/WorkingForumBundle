<?php

namespace Yosimitso\WorkingForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Post
 *
 * @package Yosimitso\WorkingForumBundle\Entity
 *
 * @ORM\Table(name="workingforum_post")
 * @ORM\Entity(repositoryClass="Yosimitso\WorkingForumBundle\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\NamedNativeQueries({
 * @ORM\NamedNativeQuery(
 *     name="last_post_by_subforum",
 *     resultClass="__CLASS__",
 *     query="SELECT * FROM `workingforum_post` WFP WHERE WFP.`thread_id` IN (SELECT WFT.`id` FROM `workingforum_thread` WFT WHERE WFT.`subforum_id` = :subforumId) ORDER BY WFP.create_date DESC LIMIT 1"
 * ),
 * @ORM\NamedNativeQuery(
 *     name="last_post_by_thread",
 *     resultClass="__CLASS__",
 *     query="SELECT * FROM `workingforum_post` WFP WHERE WFP.`thread_id` = :threadId ORDER BY WFP.create_date DESC LIMIT 1"
 * )
 * })
 */
class Post
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
     * @var Thread
     * @ORM\ManyToOne(targetEntity="Yosimitso\WorkingForumBundle\Entity\Thread", inversedBy="posts")
     * @ORM\JoinColumn(name="thread_id", referencedColumnName="id", nullable=true)
     */
    private $thread;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank(message="post.not_blank")
     */
    private $content;

    /**
     * @var boolean
     *
     * @ORM\Column(name="published", type="boolean")
     */
    private $published;

    /**
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="Yosimitso\WorkingForumBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime")
     * @Assert\NotBlank()
     */
    private $createDate;

    /** var string
     *
     * @ORM\Column(name="ip_address", type="string")
     */
    private $ipAddress;


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Yosimitso\WorkingForumBundle\Entity\PostReport",
     *     mappedBy="post",
     *     cascade={"remove"}
     * )
     */

    private $postReports;

    /**
     * @ORM\OneToMany(targetEntity="Yosimitso\WorkingForumBundle\Entity\PostFile", mappedBy="post", cascade={"persist", "remove"})
     *
     * @var ArrayCollection
     */
    private $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }


    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Thread $thread
     *
     * @return Post
     */
    public function setThread(Thread $thread)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param string $content
     *
     * @return Post
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * @param bool $published
     *
     * @return Post
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     *
     * @return Post
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param \DateTimeInterface $createDate
     *
     * @return Post
     */
    public function setCreateDate(\DateTimeInterface $createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param mixed $ipAddress
     *
     * @return Post
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPostReports()
    {
        return $this->postReports;
    }

    /**
     * @return ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param $files PostFile[]
     *
     * @return $this
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @return Post
     */
    public function addFile()
    {
        $this->files->add($file);

        return $this;
    }


    /**
     * @ORM\PrePersist
     */
    public function prePersistTimestamps()
    {
        $this->setCreateDate(new \DateTimeImmutable());
    }

    public function getReviews() : ArrayCollection
    {
        return $this->getPostReports()->filter(function (PostReport $report) {

            $review = $report->getReview();

            return !empty($review) && $review->getType() === '2';

        });
    }
}
