<?php

namespace Yosimitso\WorkingForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class PostReport
 *
 * @package Yosimitso\WorkingForumBundle\Entity
 *
 * @ORM\Table(name="workingforum_post_report")
 * @ORM\Entity(repositoryClass="Yosimitso\WorkingForumBundle\Repository\PostReportRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\NamedNativeQueries({
 * @ORM\NamedNativeQuery(name="post_report_non_reviewed", resultClass="__CLASS__", query="select * from workingforum_post_report where id not in (select report_id from workingforum_post_report_review)")
 * })
 */
class PostReport
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
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Yosimitso\WorkingForumBundle\Entity\Post", inversedBy="postReport")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", nullable=false)
     */
    private $post;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Yosimitso\WorkingForumBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime")
     */
    private $createDate;

    /**
     * @var PostReportReview
     * @ORM\OneToOne(targetEntity="PostReportReview", mappedBy="report")
     */
    private $review;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param int $post
     *
     * @return PostReport
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $user
     *
     * @return PostReport
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param \DateTimeInterface $createDate
     *
     * @return PostReport
     */
    public function setCreateDate(\DateTimeInterface $createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * @return PostReportReview
     */
    public function getReview(): ?PostReportReview
    {
        return $this->review;
    }

    /**
     * @param PostReportReview $review
     */
    public function setReview(?PostReportReview $review): self
    {
        $this->review = $review;

        return $this;
    }


    /**
     * @ORM\PrePersist
     */
    public function prePersistTimestamps()
    {
        $this->setCreateDate(new \DateTimeImmutable());
    }
}
