<?php

namespace Capco\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Capco\AppBundle\Entity\Step;

/**
 * Consultation
 *
 * @ORM\Table(name="consultation")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\ConsultationRepository")
 */
class Consultation
{

    const SORT_ORDER_CREATED_AT = 0;
    const SORT_ORDER_VOTES_COUNT = 1;

    public static $sortOrder = [
        'date' => self::SORT_ORDER_CREATED_AT,
        'popularity' => self::SORT_ORDER_VOTES_COUNT,
    ];
    public static $sortOrderLabels = [
        'date' => 'idea.sort.created_at',
        'popularity' => 'idea.sort.popularity',
    ];

    const OPENING_STATUS_FUTURE = 0;
    const OPENING_STATUS_OPENED = 1;
    const OPENING_STATUS_ENDED = 2;

    public static $openingStatuses = [
        'future' => self::OPENING_STATUS_FUTURE,
        'opened' => self::OPENING_STATUS_OPENED,
        'ended' => self::OPENING_STATUS_ENDED,
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
     * @ORM\Column(name="title", type="string", length=100)
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
     * @ORM\Column(name="teaser", type="text")
     */
    private $teaser;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var boolean
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
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="opinion_count", type="integer")
     */
    private $opinionCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="argument_count", type="integer")
     */
    private $argumentCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="participant_count", type="integer")
     */
    private $participantCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="contribution_count", type="integer")
     */
    private $contributionCount = 0;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $Author;

    /**
     * @var
     * @ORM\ManyToMany(targetEntity="Capco\AppBundle\Entity\Theme", mappedBy="Consultations", cascade={"persist"})
     */
    private $Themes;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Opinion", mappedBy="Consultation",  cascade={"persist", "remove"})
     */
    private $Opinions;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Step", mappedBy="consultation",  cascade={"persist", "remove"}, orphanRemoval = true)
     */
    private $Steps;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Capco\MediaBundle\Entity\Media")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id")
     */
    private $Media;

    public function __toString()
    {
        if ($this->id) {
            return $this->getTitle();
        } else {
            return "New consultation";
        }

        $this->Themes = new ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Consultation
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getOpinionCount()
    {
        return $this->opinionCount;
    }

    /**
     * @param int $opinionCount
     */
    public function setOpinionCount($opinionCount)
    {
        $this->opinionCount = $opinionCount;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
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
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getTeaser()
    {
        return $this->teaser;
    }

    /**
     * @param string $teaser
     */
    public function setTeaser($teaser)
    {
        $this->teaser = $teaser;
    }

    /**
     * @return int
     */
    public function getArgumentCount()
    {
        return $this->argumentCount;
    }

    /**
     * @param int $argumentCount
     */
    public function setArgumentCount($argumentCount)
    {
        $this->argumentCount = $argumentCount;
    }

    /**
     * @return mixed
     */
    public function getMedia()
    {
        return $this->Media;
    }

    /**
     * @param mixed $media
     */
    public function setMedia($Media)
    {
        $this->Media = $Media;
    }

    public function getConsultationStep() {
        foreach ($this->Steps as $step) {
            if($step->isConsultationStep()) {
                return $step;
            }
        }
        return null;
    }

    public function getOpenedAt(){
        $consultationStep = $this->getConsultationStep();
        if(null != $consultationStep){
            return $consultationStep->getStartAt();
        }
        return null;
    }

    public function getClosedAt(){
        $consultationStep = $this->getConsultationStep();
        if(null != $consultationStep){
            return $consultationStep->getEndAt();
        }
        return null;
    }

    public function getRemainingDays()
    {
        $closedAt = $this->getClosedAt();
        $now = new \DateTime();
        if(null != $closedAt && $closedAt > $now){
            return $closedAt->diff($now)->format('%a');
        }
        else {
            return null;
        }
    }

    public function getOpeningStatus()
    {
        $now = new \DateTime();
        $closedAt = $this->getClosedAt();
        $openedAt = $this->getOpenedAt();

        if (null != $closedAt && $now > $closedAt) {
            return self::OPENING_STATUS_ENDED;
        }
        else if (null != $openedAt && $now < $openedAt) {
            return self::OPENING_STATUS_FUTURE;
        }
        else if(null != $openedAt && null!= $closedAt && $openedAt < $now && $now < $closedAt) {
            return self::OPENING_STATUS_OPENED;
        }
        return null;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Themes = new ArrayCollection();
        $this->Opinions = new ArrayCollection();
        $this->Problems = new ArrayCollection();
        $this->Steps = new ArrayCollection();
    }

    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     *
     * @return Consultation
     */
    public function setIsEnabled($isEnabled)
    {
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

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Consultation
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Consultation
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Add theme
     *
     * @param \Capco\AppBundle\Entity\Theme $theme
     *
     * @return Consultation
     */
    public function addTheme(\Capco\AppBundle\Entity\Theme $theme)
    {
        $this->Themes[] = $theme;

        return $this;
    }

    /**
     * Remove theme
     *
     * @param \Capco\AppBundle\Entity\Theme $theme
     */
    public function removeTheme(\Capco\AppBundle\Entity\Theme $theme)
    {
        $this->Themes->removeElement($theme);
    }

    /**
     * Get themes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getThemes()
    {
        return $this->Themes;
    }

    /**
     * Add step
     *
     * @param \Capco\AppBundle\Entity\Step $step
     *
     * @return Consultation
     */
    public function addStep(Step $step)
    {
        $step->setConsultation($this);
        $this->Steps->add($step);

        return $this;
    }

    /**
     * Remove step
     *
     * @param \Capco\AppBundle\Entity\Step $step
     */
    public function removeStep(Step $step)
    {
        $step->setConsultation(null);
        $this->Steps->removeElement($step);
    }

    /**
     * Get steps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSteps()
    {
        return $this->Steps;
    }

    /**
     * Set steps
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $steps
     * @return Consultation
     */
    public function setSteps($steps)
    {
        if (gettype($steps) == "array") {
            $steps = new ArrayCollection($steps);
        }

        foreach($steps as $step)
        {
            $step->setConsultation($this);
        }
        $this->Steps = $steps;
        return $this;
    }

    /**
     *
     * @param \Capco\AppBundle\Entity\Opinion $opinion
     *
     * @return Opinion
     */
    public function addOpinion(Opinion $opinion)
    {
        $this->Opinions[] = $opinion;

        return $this;
    }

    /**
     *
     * @param \Capco\AppBundle\Entity\Opinion $opinion
     */
    public function removeOpinion(Opinion $opinion)
    {
        $this->Opinions->removeElement($opinion);
    }

    /**
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOpinions()
    {
        return $this->Opinions;
    }

    /**
     * Get theme names
     *
     * @return array
     */
    public function getThemeNames()
    {
        $return = array();

        foreach ($this->Themes as $theme) {
            $return[] = $theme->getTitle();
        }

        sort($return);
        return $return;
    }

    /**
     * Set participantCount
     *
     * @param integer $participantCount
     *
     * @return Consultation
     */
    public function setParticipantCount($participantCount)
    {
        $this->participantCount = $participantCount;

        return $this;
    }

    /**
     * Get participantCount
     *
     * @return integer
     */
    public function getParticipantCount()
    {
        return $this->participantCount;
    }

    /**
     * Set contributionCount
     *
     * @param integer $contributionCount
     *
     * @return Consultation
     */
    public function setContributionCount($contributionCount)
    {
        $this->contributionCount = $contributionCount;

        return $this;
    }

    /**
     * Get contributionCount
     *
     * @return integer
     */
    public function getContributionCount()
    {
        return $this->contributionCount;
    }
}
