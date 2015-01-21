<?php

namespace Capco\AppBundle\Entity;

use Capco\UserBundle\Entity\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Argument
 *
 * @ORM\Table(name="argument")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\ArgumentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Argument
{

    const TYPE_AGAINST = 0;
    const TYPE_FOR  = 1;

    public static $argumentTypes = [
        self::TYPE_FOR => 'yes',
        self::TYPE_AGAINST => 'no'
   ];

    public static $argumentTypesLabels = [
        self::TYPE_FOR => 'argument.show.type.for',
        self::TYPE_AGAINST => 'argument.show.type.against',
    ];

    public static $sortCriterias = [
        'date' => 'argument.sort.date',
        'popularity' => 'argument.sort.popularity',
    ];

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Assert\NotBlank()
     */
    private $body;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="change", field={"body"})
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="vote_count", type="integer")
     */
    private $voteCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type = 1;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_trashed", type="boolean")
     */
    private $isTrashed = false;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="change", field={"isTrashed"})
     * @ORM\Column(name="trashed_at", type="datetime", nullable=true)
     */
    private $trashedAt = null;

    /**
     * @var string
     *
     * @ORM\Column(name="trashed_reason", type="text", nullable=true)
     */
    private $trashedReason = null;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $Author;

    /**
     * @var
     *
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\ArgumentVote", mappedBy="argument", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $Votes;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Opinion", inversedBy="arguments", cascade={"persist"})
     */
    private $opinion;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Reporting", mappedBy="Argument", cascade={"persist", "remove"})
     */
    private $Reports;

    function __construct()
    {
        $this->Votes = new ArrayCollection();
        $this->Reports = new ArrayCollection();
        $this->updatedAt = new \Datetime();
        $this->voteCount = 0;
    }

    public function __toString()
    {
        if ($this->id) {
            return $this->getBodyExcerpt(50);
        } else {
            return "New opinion";
        }
    }

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
     * Set body
     *
     * @param string $body
     * @return Argument
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set voteCount
     *
     * @param integer $voteCount
     * @return Argument
     */
    public function setVoteCount($voteCount)
    {
        $this->voteCount = $voteCount;

        return $this;
    }

    /**
     * Get voteCount
     *
     * @return integer
     */
    public function getVoteCount()
    {
        return $this->voteCount;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getOpinion()
    {
        return $this->opinion;
    }

    /**
     * @param mixed $opinion
     */
    public function setOpinion($opinion)
    {
        if ($this->opinion != null) {
            $this->opinion->removeArgument($this);
        }
        $this->opinion = $opinion;
        $opinion->addArgument($this);
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->Author;
    }

    /**
     * @param mixed $Author
     */
    public function setAuthor($Author)
    {
        $this->Author = $Author;
    }

    /**
     * Set isTrashed
     *
     * @param boolean $isTrashed
     * @return Argument
     */
    public function setIsTrashed($isTrashed)
    {
        if ($isTrashed != $this->isTrashed) {
            if($this->isEnabled) {
                if ($isTrashed) {
                    $this->opinion->getConsultation()->increaseTrashedArgumentCount(1);
                    $this->opinion->decreaseArgumentsCount(1);
                } else {
                    $this->opinion->increaseArgumentsCount(1);
                    $this->opinion->getConsultation()->decreaseTrashedArgumentCount(1);
                }
            }
        }
        $this->isTrashed = $isTrashed;
        return $this;
    }

    /**
     * Get isTrashed
     *
     * @return boolean
     */
    public function getIsTrashed()
    {
        return $this->isTrashed;
    }

    /**
     * Set trashedAt
     *
     * @param \DateTime $trashedAt
     * @return Argument
     */
    public function setTrashedAt($trashedAt)
    {
        $this->trashedAt = $trashedAt;

        return $this;
    }

    /**
     * Get trashedAt
     *
     * @return \DateTime
     */
    public function getTrashedAt()
    {
        return $this->trashedAt;
    }

    /**
     * Set trashedReason
     *
     * @param string $trashedReason
     * @return Argument
     */
    public function setTrashedReason($trashedReason)
    {
        $this->trashedReason = $trashedReason;

        return $this;
    }

    /**
     * Get trashedReason
     *
     * @return string
     */
    public function getTrashedReason()
    {
        return $this->trashedReason;
    }

    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     * @return Argument
     */
    public function setIsEnabled($isEnabled)
    {
        if ($isEnabled != $this->isEnabled) {
            if($isEnabled) {
                if($this->isTrashed) {
                    $this->opinion->getConsultation()->increaseTrashedArgumentCount(1);
                } else {
                    $this->opinion->increaseArgumentsCount(1);
                }
            } else {
                if($this->isTrashed) {
                    $this->opinion->getConsultation()->decreaseArgumentCount(1);
                } else {
                    $this->opinion->decreaseArgumentsCount(1);
                }
            }
        }
        $this->isEnabled = $isEnabled;
        return $this;
    }

    /**
     * Get isEnabled
     *
     * @return boolean
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    public function getVotes(){
        return $this->Votes;
    }

    public function addVote($vote){
        $this->voteCount++;
        $this->Votes->add($vote);
        return $this;
    }

    public function removeVote($vote)
    {
        if ($this->Votes->removeElement($vote)) {
            $this->voteCount--;
        }

        return $this;
    }

    public function resetVotes()
    {
        foreach ($this->Votes as $vote) {
            $this->removeVote($vote);
            $vote->setArgument(null);
        }
    }

    public function userHasVote(User $user = null)
    {
        if ($user != null) {
            foreach($this->Votes as $vote){
                if($vote->getVoter() == $user){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getReports()
    {
        return $this->Reports;
    }

    /**
     * @param Reporting $report
     * @return $this
     */
    public function addReport(Reporting $report)
    {
        $this->Reports->add($report);
        return $this;
    }

    /**
     * @param Reporting $report
     * @return $this
     */
    public function removeReport(Reporting $report)
    {
        $this->Reports->removeElement($report);
        return $this;
    }

    public function canDisplay() {
        return ($this->isEnabled && $this->opinion->canDisplay());
    }

    public function canContribute()
    {
        return ($this->isEnabled && !$this->isTrashed && $this->opinion->canContribute());
    }

    public function getBodyExcerpt($nb = 100)
    {
        $excerpt = substr($this->body, 0, $nb);
        $excerpt = $excerpt.'...';
        return $excerpt;
    }

    /**
     * @ORM\PreRemove
     */
    public function deleteArgument()
    {
        if ($this->opinion != null) {
            $this->opinion->removeArgument($this);
        }

    }
}
