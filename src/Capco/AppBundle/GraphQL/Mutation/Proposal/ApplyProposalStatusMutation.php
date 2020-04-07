<?php


namespace Capco\AppBundle\GraphQL\Mutation\Proposal;


use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\Selection;
use Capco\AppBundle\Entity\Status;
use Capco\UserBundle\Entity\User;
use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class ApplyProposalStatusMutation extends AbstractProposalStepMutation implements MutationInterface
{
    public function __invoke(Argument $args, User $user): array
    {
        $error = null;
        $proposals = [];
        try {
            $status = $this->getStatus($args->offsetGet('statusId'), $user);
            $proposals = $this->getProposals($args->offsetGet('proposalIds'), $user);
            $this->applyStatusToSeveralProposals($proposals, $status);
        } catch (UserError $userError) {
            $error = $userError->getMessage();
        }

        return [
            'proposals' => $this->getConnection($proposals, $args),
            'error' => $error
        ];
    }

    private function applyStatusToSeveralProposals(array $proposals, ?Status $status): void
    {
        $changedProposals = [];
        foreach ($proposals as $proposal) {
            if ($this->applyStatusToOneProposal($proposal, $status)) {
                $changedProposals[] = $proposal;
            }
        }
        $this->entityManager->flush();
        $this->publish($changedProposals);
    }

    private function applyStatusToOneProposal(Proposal $proposal, ?Status $status): bool
    {
        foreach ($proposal->getSelections() as $selection) {
            if ($this->applyStatusToSelection($selection, $status)) {
                return true;
            }
        }

        return false;
    }

    private function applyStatusToSelection(Selection $selection, ?Status $status): bool
    {
        if ((is_null($status) || ($status->getStep() === $selection->getStep()))
            && $selection->getStatus() !== $status
        ) {
            $selection->setStatus($status);
            $this->entityManager->persist($selection);
            return true;
        }

        return false;

    }
}
