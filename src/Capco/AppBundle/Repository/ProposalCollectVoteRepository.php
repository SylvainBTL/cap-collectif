<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ProposalCollectVoteRepository extends EntityRepository
{
    public function getAnonymousCount(): int
    {
        $qb = $this->createQueryBuilder('v')
        ->select('count(DISTINCT v.email)')
        ->where('v.user IS NULL')
    ;

        return $qb->getQuery()
        ->getSingleScalarResult()
        ;
    }

    public function countByAuthorAndProject(User $author, Project $project): int
    {
        return $this->createQueryBuilder('pv')
        ->select('COUNT(DISTINCT pv)')
        ->andWhere('pv.user = :author')
        ->andWhere('pv.expired = false')
        ->leftJoin('pv.proposal', 'proposal')
        ->andWhere('proposal.deletedAt IS NULL')
        ->andWhere('pv.collectStep IN (:steps)')
        ->setParameter('steps', array_map(function ($step) {
            return $step;
        }, $project->getRealSteps()))
        ->setParameter('author', $author)
        ->getQuery()
        ->getSingleScalarResult()
      ;
    }

    public function countByAuthorAndStep(User $author, CollectStep $step): int
    {
        return $this->createQueryBuilder('pv')
        ->select('COUNT(DISTINCT pv)')
        ->andWhere('pv.collectStep = :step')
        ->andWhere('pv.user = :author')
        ->andWhere('pv.expired = false')
        ->leftJoin('pv.proposal', 'proposal')
        ->andWhere('proposal.deletedAt IS NULL')
        ->setParameter('author', $author)
        ->setParameter('step', $step)
        ->getQuery()
        ->getSingleScalarResult()
      ;
    }

    public function getVotesByStepAndUser(CollectStep $step, User $user)
    {
        return $this->createQueryBuilder('pv')
          ->select('pv', 'proposal')
          ->andWhere('pv.collectStep = :step')
          ->andWhere('pv.user = :user')
          ->andWhere('pv.expired = false')
          ->leftJoin('pv.proposal', 'proposal')
          ->andWhere('proposal.id IS NOT NULL')
          ->andWhere('proposal.deletedAt IS NULL')
          ->setParameter('user', $user)
          ->setParameter('step', $step)
          ->getQuery()
          ->getResult()
        ;
    }

    public function getUserVotesGroupedByStepIds(array $collectStepsIds, User $user = null): array
    {
        $userVotes = [];
        if ($user) {
            foreach ($collectStepsIds as $id) {
                $qb = $this->createQueryBuilder('pv')
              ->select('proposal.id')
              ->andWhere('pv.collectStep = :id')
              ->andWhere('pv.user = :user')
              ->leftJoin('pv.proposal', 'proposal')
              ->andWhere('proposal.deletedAt IS NULL')
              ->setParameter('user', $user)
              ->setParameter('id', $id)
              ;
                $results = $qb->getQuery()->getScalarResult();
                $userVotes[$id] = array_map(function ($id) {
                    return $id;
                }, array_column($results, 'id'));
            }
        }

        foreach ($collectStepsIds as $id) {
            if (!array_key_exists($id, $userVotes)) {
                $userVotes[$id] = [];
            }
        }

        return $userVotes;
    }

    public function countVotesByStepAndUser(CollectStep $step, User $user)
    {
        return $this->createQueryBuilder('pv')
          ->select('COUNT(pv.id)')
          ->andWhere('pv.expired = 0')
          ->andWhere('pv.collectStep = :collectStep')
          ->andWhere('pv.user = :user')
          ->setParameter('collectStep', $step)
          ->setParameter('user', $user)
          ->getQuery()
          ->getSingleScalarResult()
      ;
    }

    public function getCountsByProposalGroupedByStepsId(Proposal $proposal)
    {
        return $this->getCountsByProposalGroupedBySteps($proposal);
    }

    public function getCountsByProposalGroupedByStepsTitle(Proposal $proposal)
    {
        return $this->getCountsByProposalGroupedBySteps($proposal, true);
    }

    public function getVotesForProposalByStepId(Proposal $proposal, string $stepId, $limit = null, $offset = 0)
    {
        $qb = $this->createQueryBuilder('pv')
            ->leftJoin('pv.collectStep', 'cs')
            ->where('pv.proposal = :proposal')
            ->andWhere('pv.expired = false')
            ->setParameter('proposal', $proposal)
            ->andWhere('cs.id = :stepId')
            ->setParameter('stepId', $stepId)
            ->addOrderBy('pv.createdAt', 'DESC')
        ;

        if ($limit) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function getVotesCountForCollectStep(CollectStep $step, $themeId = null, $districtId = null)
    {
        $qb = $this->createQueryBuilder('pv')
            ->select('COUNT(pv.id)')
            ->leftJoin('pv.proposal', 'p')
            ->andWhere('pv.collectStep = :step')
            ->setParameter('step', $step)
        ;

        if ($themeId) {
            $qb
                ->leftJoin('p.theme', 't')
                ->andWhere('t.id = :themeId')
                ->setParameter('themeId', $themeId)
            ;
        }

        if ($districtId) {
            $qb
                ->leftJoin('p.district', 'd')
                ->andWhere('d.id = :districtId')
                ->setParameter('districtId', $districtId)
            ;
        }

        return (int) ($qb->getQuery()->getSingleScalarResult());
    }

    public function getVotesForProposal(Proposal $proposal, ?int $limit = null, ?string $field, int $offset = 0, ?string $direction = 'ASC'): Paginator
    {
        $query = $this->createQueryBuilder('pv')
            ->andWhere('pv.proposal = :proposal')
            ->setParameter('proposal', $proposal)
        ;

        if ('CREATED_AT' === $field) {
            $query->addOrderBy('pv.createdAt', $direction);
        }

        if ($limit) {
            $query->setMaxResults($limit);
            $query->setFirstResult($offset);
        }

        return new Paginator($query);
    }

    public function countVotesForProposal(Proposal $proposal): int
    {
        return (int) $this->createQueryBuilder('pv')
            ->select('COUNT(pv.id)')
            ->andWhere('pv.proposal = :proposal')
            ->setParameter('proposal', $proposal)
            ->getQuery()->getSingleScalarResult()
            ;
    }

    private function getCountsByProposalGroupedBySteps(Proposal $proposal, bool $asTitle = false): array
    {
        if (!$proposal->getProposalForm()->getStep()) {
            return [];
        }

        $qb = $this->createQueryBuilder('pv');

        if ($asTitle) {
            $qb->select('COUNT(pv.id) as votesCount', 'cs.title as stepId');
            $index = $proposal->getProposalForm()->getStep()->getTitle();
        } else {
            $qb->select('COUNT(pv.id) as votesCount', 'cs.id as stepId');
            $index = $proposal->getProposalForm()->getStep()->getId();
        }

        $qb
            ->leftJoin('pv.collectStep', 'cs')
            ->andWhere('pv.proposal = :proposal')
            ->andWhere('pv.expired = false')
            ->setParameter('proposal', $proposal)
            ->groupBy('pv.collectStep');

        $results = $qb->getQuery()->getResult();
        $votesBySteps = [];
        foreach ($results as $result) {
            $votesBySteps[$result['stepId']] = (int) ($result['votesCount']);
        }

        if (!array_key_exists($index, $votesBySteps)) {
            $votesBySteps[$index] = 0;
        }

        return $votesBySteps;
    }
}
