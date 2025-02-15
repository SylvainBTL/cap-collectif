<?php

namespace Capco\AppBundle\Entity\Steps;

use Capco\AppBundle\Entity\Interfaces\ParticipativeStepInterface;
use Capco\AppBundle\Entity\Interfaces\VotableStepInterface;
use Capco\AppBundle\Entity\ProposalForm;
use Capco\AppBundle\Entity\Selection;
use Capco\AppBundle\Entity\Status;
use Capco\AppBundle\Enum\ProposalSort;
use Capco\AppBundle\Traits\AllowAuthorsToAddNewsTrait;
use Capco\AppBundle\Traits\SecretBallotTrait;
use Capco\AppBundle\Traits\TimelessStepTrait;
use Capco\AppBundle\Traits\VoteSmsTrait;
use Capco\AppBundle\Traits\VoteThresholdTrait;
use Capco\AppBundle\Traits\VoteTypeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Capco\AppBundle\Validator\Constraints as CapcoAssert;

/**
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\SelectionStepRepository")
 * @CapcoAssert\VoteMin
 */
class SelectionStep extends AbstractStep implements ParticipativeStepInterface, VotableStepInterface
{
    use AllowAuthorsToAddNewsTrait;
    use SecretBallotTrait;
    use TimelessStepTrait;
    use VoteThresholdTrait;
    use VoteTypeTrait;
    use VoteSmsTrait;

    public const TYPE = 'selection';
    const VOTE_TYPE_DISABLED = 0;
    const VOTE_TYPE_SIMPLE = 1;
    const VOTE_TYPE_BUDGET = 2;

    public static $voteTypeLabels = [
        'step.selection.vote_type.disabled' => self::VOTE_TYPE_DISABLED,
        'step.selection.vote_type.simple' => self::VOTE_TYPE_SIMPLE,
        'step.selection.vote_type.budget' => self::VOTE_TYPE_BUDGET,
    ];

    public static $sort = [
        'old',
        'last',
        'votes',
        'least-votes',
        'comments',
        'random',
        'expensive',
        'cheap',
    ];

    public static $sortLabels = [
        'global.filter_f_comments' => 'comments',
        'global.filter_f_last' => 'last',
        'global.filter_f_old' => 'old',
        'global.random' => 'random',
        'step.sort.votes' => 'votes',
        'step.sort.least-votes' => 'least-votes',
        'step.sort.expensive' => 'expensive',
        'step.sort.cheap' => 'cheap',
    ];

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Selection", mappedBy="selectionStep", cascade={"persist"}, orphanRemoval=true)
     */
    private $selections;

    /**
     * @ORM\Column(name="allowing_progess_steps", type="boolean", options={"default": false})
     */
    private $allowingProgressSteps = false;

    /**
     * @ORM\Column(name="default_sort", type="string", nullable=false)
     * @Assert\Choice(choices={"old","last","votes","least-votes","comments","random", "cheap", "expensive"})
     */
    private $defaultSort = ProposalSort::RANDOM;

    /**
     * @ORM\OneToOne(targetEntity="Capco\AppBundle\Entity\Status")
     * @ORM\JoinColumn(name="default_status_id", nullable=true, onDelete="SET NULL")
     */
    private $defaultStatus;

    public function __construct()
    {
        parent::__construct();
        $this->selections = new ArrayCollection();
    }

    public function addSelection(Selection $selection)
    {
        if (!$this->selections->contains($selection)) {
            $this->selections[] = $selection;
            $selection->setSelectionStep($this);
        }

        return $this;
    }

    public function removeSelection(Selection $selection)
    {
        $this->selections->removeElement($selection);
    }

    public function getSelections()
    {
        return $this->selections;
    }

    public function getDefaultSort(): string
    {
        return $this->defaultSort;
    }

    /**
     * @param mixed $defaultSort
     *
     * @return $this
     */
    public function setDefaultSort($defaultSort)
    {
        $this->defaultSort = $defaultSort;

        return $this;
    }

    public function getDefaultStatus(): ?Status
    {
        return $this->defaultStatus;
    }

    public function setDefaultStatus(?Status $defaultStatus): self
    {
        $this->defaultStatus = $defaultStatus;

        return $this;
    }

    public function isAllowingProgressSteps(): bool
    {
        return $this->allowingProgressSteps;
    }

    public function setAllowingProgressSteps(bool $allowingProgressSteps): self
    {
        $this->allowingProgressSteps = $allowingProgressSteps;

        return $this;
    }

    public function getType()
    {
        return self::TYPE;
    }

    public function isSelectionStep(): bool
    {
        return true;
    }

    public function getProposalForm(): ProposalForm
    {
        if ($this->getProject()) {
            /** @var ProjectAbstractStep $step */
            $step = (new ArrayCollection($this->getProject()->getExportableSteps()))
                ->filter(function (ProjectAbstractStep $step) {
                    return $step->getStep()->isCollectStep();
                })
                ->first();

            if ($step && $step->getStep() && $step->getStep()->getProposalForm()) {
                return $step->getStep()->getProposalForm();
            }
        }

        throw new \RuntimeException(
            $this->getId() . ' : no proposalForm found for this selection step'
        );
    }

    public function getProposalFormId()
    {
        return $this->getProposalForm()->getId();
    }

    public function getProposals()
    {
        $proposals = [];
        foreach ($this->selections as $selection) {
            $proposals[] = $selection->getProposal();
        }

        return $proposals;
    }

    public function canShowProposals(): bool
    {
        return $this->getStartAt() <= new \DateTime();
    }

    public function isParticipative(): bool
    {
        return true;
    }

    public function isAnalysisStep(): bool
    {
        if (
            $this->getProposalForm()->getAnalysisConfiguration() &&
            $this->getProposalForm()
                ->getAnalysisConfiguration()
                ->getAnalysisStep()
        ) {
            return $this ===
                $this->getProposalForm()
                    ->getAnalysisConfiguration()
                    ->getAnalysisStep();
        }

        return false;
    }

    public function useAddressOrMap(): bool
    {
        if ($this->getProposalForm() && $this->getProposalForm()->isMapViewEnabled()) {
            return true;
        }
        if ($this->getProposalForm() && $this->getProposalForm()->getUsingAddress()) {
            return true;
        }
        foreach ($this->getRequirements() as $requirement) {
            if ('POSTAL_ADDRESS' === $requirement->getType()) {
                return true;
            }
        }

        return false;
    }
}
