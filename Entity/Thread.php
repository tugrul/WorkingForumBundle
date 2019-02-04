<?php

namespace Yosimitso\WorkingForumBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Thread
 *
 * @ORM\Table(name="workingforum_thread")
 * @ORM\Entity(repositoryClass="Yosimitso\WorkingForumBundle\Repository\ThreadRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\NamedNativeQueries({
 * @ORM\NamedNativeQuery(
 *     name="thread_list_updated_by_subforum",
 *     query="select * from workingforum_thread WFT inner join (select thread_id, max(id) as last_post_id from workingforum_post group by thread_id) LPI on WFT.id = LPI.thread_id inner join workingforum_post WFP on WFP.id = LPI.last_post_id where WFT.subforum_id = :subforumId",
 *     resultSetMapping="thread_list_updated_mapping"
 * )})
 */
class Thread implements SlugableInterface
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
     * @var Subforum
     *
     * @ORM\ManyToOne(targetEntity="Yosimitso\WorkingForumBundle\Entity\Subforum", inversedBy="thread")
     * @ORM\JoinColumn(name="subforum_id", referencedColumnName="id", nullable=false)
     */
    private $subforum;

    /**
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="Yosimitso\WorkingForumBundle\Entity\User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=false)
     */
    private $author;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="create_date", type="datetime")
     */
    private $createDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="resolved", type="boolean", nullable=true)
     */
    private $resolved;

    /**
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean", nullable=true)
     */
    private $locked;

    /**
     * @var string
     * @ORM\Column(name="label", type="string")
     * @Assert\NotBlank(message="thread.label.not_blank")
     * @Assert\Length(
     *     min=5,
     *     minMessage="thread.label.min_length",
     *     max=50,
     *     maxMessage="thread.label.max_length"
     * )
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="sublabel", type="string")
     */
    private $subLabel;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", nullable=true)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="Yosimitso\WorkingForumBundle\Entity\Post", mappedBy="thread", cascade={"persist","remove"})
     *
     * @var ArrayCollection
     */
    private $posts;

    /**
     * @var boolean
     * @ORM\Column(name="pin", type="boolean", nullable=true)
     */
    private $pin = false;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * @param Subforum $subforum
     *
     * @return Thread
     */
    public function setSubforum(Subforum $subforum)
    {
        $this->subforum = $subforum;

        return $this;
    }

    /**
     * @return Subforum
     */
    public function getSubforum()
    {
        return $this->subforum;
    }

    /**
     * @param \DateTimeInterface $createDate
     *
     * @return Thread
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

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
     * @param boolean $resolved
     *
     * @return Thread
     */
    public function setResolved($resolved)
    {
        $this->resolved = $resolved;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getResolved()
    {
        return $this->resolved;
    }

    /**
     * @param boolean $locked
     *
     * @return Thread
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * @param string $label
     *
     * @return Thread
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $subLabel
     *
     * @return Thread
     */
    public function setSublabel($subLabel)
    {
        $this->subLabel = $subLabel;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubLabel()
    {
        return $this->subLabel;
    }

    /**
     * @return UserInterface
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param UserInterface $author
     *
     * @return Thread
     */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @param string $slug
     *
     * @return Thread
     */
    public function setSlug(?string $slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getSlugProvider(): string
    {
        return $this->getLabel();
    }

    /**
     * @param $post
     *
     * @return Thread
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param Post $post
     *
     * @return $this
     */
    public function addPost(Post $post)
    {
        $this->posts->add($post);

        return $this;
    }

    /**
     * @param boolean $pin
     *
     * @return Thread
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersistTimestamps()
    {
        $this->setCreateDate(new \DateTimeImmutable());
    }
}
