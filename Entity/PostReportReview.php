<?php

namespace Yosimitso\WorkingForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class PostReportReview
 * @package Yosimitso\WorkingForumBundle\Entity
 *
 * @ORM\Table(name="workingforum_post_report_review")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class PostReportReview
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
     * @var PostReport
     *
     * @ORM\OneToOne(targetEntity="PostReport")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="Yosimitso\WorkingForumBundle\Entity\User")
     * @ORM\JoinColumn(name="reviewer_id", referencedColumnName="id")
     */
    private $reviewer;

    /**
     * @var integer
     * @ORM\Column(name="type_id", type="smallint")
     */
    private $type;

    /**
     * @var string
     * @ORM\Column(name="moderate_reason", type="text", nullable=true)
     */
    private $reason;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="create_date", type="datetime")
     */
    private $createDate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return PostReport
     */
    public function getReport(): PostReport
    {
        return $this->report;
    }

    /**
     * @param PostReport $report
     */
    public function setReport(PostReport $report): void
    {
        $this->report = $report;
    }

    /**
     * @return UserInterface
     */
    public function getReviewer(): UserInterface
    {
        return $this->reviewer;
    }

    /**
     * @param UserInterface $reviewer
     */
    public function setReviewer(UserInterface $reviewer): void
    {
        $this->reviewer = $reviewer;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreateDate(): \DateTimeInterface
    {
        return $this->createDate;
    }

    /**
     * @param \DateTimeInterface $createDate
     */
    public function setCreateDate(\DateTimeInterface $createDate): void
    {
        $this->createDate = $createDate;
    }



    /**
     * @ORM\PrePersist
     */
    public function prePersistTimestamps()
    {
        $this->setCreateDate(new \DateTimeImmutable());
    }
}

