<?php

namespace Capco\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Capco\UserBundle\Entity\User;

/**
 * Opinion.
 *
 * @ORM\Table(name="opinion")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\OpinionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Opinion
{
    public static $sortCriterias = [
        'votes' => 'opinion.sort.votes',
        'comments' => 'opinion.sort.comments',
        'date' => 'opinion.sort.date',
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=255)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Assert\NotBlank()
     */
    private $body;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled = true;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="change", field={"title", "body", "Author", "OpinionType", "Consultation"})
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var bool
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
     * @var int
     *
     * @ORM\Column(name="vote_count_nok", type="integer")
     */
    private $voteCountNok = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="vote_count_ok", type="integer")
     */
    private $voteCountOk = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="vote_count_mitige", type="integer")
     */
    private $voteCountMitige = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="sources_count", type="integer")
     */
    private $sourcesCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="arguments_count", type="integer")
     */
    private $argumentsCount = 0;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User", inversedBy="opinions")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $Author;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\OpinionType", inversedBy="Opinions", cascade={"persist"})
     * @ORM\JoinColumn(name="opinion_type_id", referencedColumnName="id", nullable=false)
     */
    private $OpinionType;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Consultation", inversedBy="Opinions", cascade={"persist"})
     */
    private $Consultation;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Source", mappedBy="Opinion",  cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $Sources;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Argument", mappedBy="opinion",  cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $arguments;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\OpinionVote", mappedBy="opinion", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $votes;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Reporting", mappedBy="Opinion", cascade={"persist", "remove"})
     */
    private $Reports;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
        $this->Reports = new ArrayCollection();
        $this->arguments = new ArrayCollection();
        $this->Sources = new ArrayCollection();
        $this->updatedAt = new \Datetime();

        $this->argumentsCount = 0;
        $this->sourcesCount = 0;
    }

    public function __toString()
    {
        if ($this->id) {
            return $this->getTitle();
        } else {
            return 'New opinion';
        }
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Opinion
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Opinion
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set body.
     *
     * @param string $body
     *
     * @return Opinion
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get isEnabled.
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Set isEnabled.
     *
     * @param bool $isEnabled
     *
     * @return Argument
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get isTrashed.
     *
     * @return bool
     */
    public function getIsTrashed()
    {
        return $this->isTrashed;
    }

    /**
     * Set isTrashed.
     *
     * @param bool $isTrashed
     *
     * @return Opinion
     */
    public function setIsTrashed($isTrashed)
    {
        if (false == $isTrashed) {
            $this->trashedReason = null;
            $this->trashedAt = null;
        }
        $this->isTrashed = $isTrashed;

        return $this;
    }

    /**
     * Get trashedAt.
     *
     * @return \DateTime
     */
    public function getTrashedAt()
    {
        return $this->trashedAt;
    }

    /**
     * Set trashedAt.
     *
     * @param \DateTime $trashedAt
     *
     * @return Opinion
     */
    public function setTrashedAt($trashedAt)
    {
        $this->trashedAt = $trashedAt;

        return $this;
    }

    /**
     * Get trashedReason.
     *
     * @return string
     */
    public function getTrashedReason()
    {
        return $this->trashedReason;
    }

    /**
     * Set trashedReason.
     *
     * @param string $trashedReason
     *
     * @return Opinion
     */
    public function setTrashedReason($trashedReason)
    {
        $this->trashedReason = $trashedReason;

        return $this;
    }
    /**
     * @return int
     */
    public function getVoteCountNok()
    {
        return $this->voteCountNok;
    }

    /**
     * @param $voteCountNok
     *
     * @return $this
     */
    public function setVoteCountNok($voteCountNok)
    {
        $this->voteCountNok = $voteCountNok;
    }

    /**
     * @return int
     */
    public function getVoteCountOk()
    {
        return $this->voteCountOk;
    }

    /**
     * @param int $voteCountOk
     */
    public function setVoteCountOk($voteCountOk)
    {
        $this->voteCountOk = $voteCountOk;
    }

    /**
     * @return int
     */
    public function getVoteCountMitige()
    {
        return $this->voteCountMitige;
    }

    /**
     * @param int $voteCountMitige
     */
    public function setVoteCountMitige($voteCountMitige)
    {
        $this->voteCountMitige = $voteCountMitige;
    }

    /**
     * @return int
     */
    public function getSourcesCount()
    {
        return $this->sourcesCount;
    }

    /**
     * @param int $sourcesCount
     */
    public function setSourcesCount($sourcesCount)
    {
        $this->sourcesCount = $sourcesCount;
    }

    /**
     * @return int
     */
    public function getArgumentsCount()
    {
        return $this->argumentsCount;
    }

    /**
     * @param int $argumentsCount
     */
    public function setArgumentsCount($argumentsCount)
    {
        $this->argumentsCount = $argumentsCount;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->Author;
    }

    /**
     * @param string $Author
     */
    public function setAuthor($Author)
    {
        $this->Author = $Author;
    }

    /**
     * @return mixed
     */
    public function getOpinionType()
    {
        return $this->OpinionType;
    }

    /**
     * @param mixed $OpinionType
     */
    public function setOpinionType($OpinionType)
    {
        $this->OpinionType = $OpinionType;
        $this->OpinionType->addOpinion($this);
    }

    /**
     * @return mixed
     */
    public function getConsultation()
    {
        return $this->Consultation;
    }

    /**
     * @param mixed $Consultation
     *
     * @return $this
     */
    public function setConsultation($Consultation)
    {
        $this->Consultation = $Consultation;
        $this->Consultation->addOpinion($this);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSources()
    {
        return $this->Sources;
    }

    /**
     * @param $source
     *
     * @return $this
     */
    public function addSource($source)
    {
        if (!$this->Sources->contains($source)) {
            $this->Sources->add($source);
        }

        return $this;
    }

    /**
     * @param $source
     *
     * @return $this
     */
    public function removeSource($source)
    {
        $this->Sources->removeElement($source);

        return $this;
    }

    /**
     * Get arguments.
     *
     * @return ArrayCollection
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param $argument
     *
     * @return $this
     */
    public function addArgument(Argument $argument)
    {
        if (!$this->arguments->contains($argument)) {
            $this->arguments->add($argument);
        }

        return $this;
    }

    /**
     * @param Argument $argument
     *
     * @return $this
     */
    public function removeArgument(Argument $argument)
    {
        $this->arguments->removeElement($argument);

        return $this;
    }

    /**
     * Get votes.
     *
     * @return string
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param OpinionVote $vote
     *
     * @return $this
     */
    public function addVote($vote)
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
        }

        return $this;
    }

    /**
     * @param OpinionVote $vote
     *
     * @return $this
     */
    public function removeVote(OpinionVote $vote)
    {
        if ($this->votes->removeElement($vote)) {
            $this->removeFromVotesCount($vote->getValue());
        }

        return $this;
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
     *
     * @return $this
     */
    public function addReport(Reporting $report)
    {
        if (!$this->Reports->contains($report)) {
            $this->Reports->add($report);
        }

        return $this;
    }

    /**
     * @param Reporting $report
     *
     * @return $this
     */
    public function removeReport(Reporting $report)
    {
        $this->Reports->removeElement($report);

        return $this;
    }

    // ******************************* Custom methods **************************************

    /**
     * @return $this
     */
    public function resetVotes()
    {
        foreach ($this->votes as $vote) {
            $vote->setConfirmed(false);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getVoteCountAll()
    {
        return $this->getVoteCountMitige() + $this->getVoteCountNok() + $this->getVoteCountOk();
    }

    /**
     * @param $type
     *
     * @return int
     */
    public function getArgumentsCountByType($type)
    {
        $count = 0;
        foreach ($this->arguments as $arg) {
            if (Argument::$argumentTypes[$arg->getType()] == $type) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return bool
     */
    public function canDisplay()
    {
        return $this->isEnabled && $this->Consultation->canDisplay();
    }

    /**
     * @return bool
     */
    public function canContribute()
    {
        return $this->isEnabled && !$this->isTrashed && $this->Consultation->canContribute();
    }

    /**
     * @param int $nb
     *
     * @return string
     */
    public function getBodyExcerpt($nb = 100)
    {
        $excerpt = substr($this->body, 0, $nb);
        $excerpt = $excerpt.'...';

        return $excerpt;
    }

    // ******************* Lifecycle *********************************

    /**
     * @ORM\PreRemove
     */
    public function deleteOpinion()
    {
        if ($this->Consultation != null) {
            $this->Consultation->removeOpinion($this);
        }
        if ($this->OpinionType != null) {
            $this->OpinionType->removeOpinion($this);
        }
    }
}
