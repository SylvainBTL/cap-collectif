<?php
namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\Argument;
use Capco\AppBundle\Entity\OpinionVersion;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Steps\ConsultationStep;
use Capco\AppBundle\Model\Argumentable;
use Capco\AppBundle\Traits\ContributionRepositoryTrait;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Capco\AppBundle\Entity\Interfaces\Trashable;

class ArgumentRepository extends EntityRepository
{
    use ContributionRepositoryTrait;

    public function getRecentOrdered()
    {
        $qb = $this->createQueryBuilder('a')
            ->select(
                'a.id',
                'a.createdAt',
                'a.updatedAt',
                'aut.username as author',
                'ut.name as userType',
                'a.published as published',
                'a.trashedAt as trashed',
                'c.title as project'
            )
            ->leftJoin('a.Author', 'aut')
            ->leftJoin('aut.userType', 'ut')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('o.step', 's')
            ->leftJoin('s.projectAbstractStep', 'cas')
            ->leftJoin('cas.project', 'c');
        return $qb->getQuery()->getArrayResult();
    }

    public function getArrayById(string $id)
    {
        $qb = $this->createQueryBuilder('a')
            ->select(
                'a.id',
                'a.createdAt',
                'a.updatedAt',
                'aut.username as author',
                'a.published as published',
                'a.trashedAt as trashed',
                'a.body as body',
                'c.title as project'
            )
            ->leftJoin('a.Author', 'aut')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('o.step', 's')
            ->leftJoin('s.projectAbstractStep', 'cas')
            ->leftJoin('cas.project', 'c')
            ->where('a.id = :id')
            ->setParameter('id', $id);
        return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
    }

    public function getUnpublishedByContributionAndTypeAndAuthor(
        Argumentable $contribution,
        int $type = null,
        User $author
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.published = false')
            ->andWhere('a.Author = :author')
            ->setParameter('author', $author);
        if (null !== $type) {
            $qb->andWhere('a.type = :type')->setParameter('type', $type);
        }
        if ($contribution instanceof Opinion) {
            $qb->andWhere('a.opinion = :opinion')->setParameter('opinion', $contribution);
        }
        if ($contribution instanceof OpinionVersion) {
            $qb
                ->andWhere('a.opinionVersion = :opinionVersion')
                ->setParameter('opinionVersion', $contribution);
        }

        return $qb->getQuery()->getResult();
    }

    public function getByContributionAndType(
        Argumentable $contribution,
        ?int $type = null,
        ?int $limit = null,
        ?int $first = null,
        string $field,
        string $direction
    ): Paginator {
        $qb = $this->getByContributionQB($contribution);

        if (null !== $type) {
            $qb->andWhere('a.type = :type')->setParameter('type', $type);
        }

        if ('PUBLISHED_AT' === $field) {
            $qb->addOrderBy('a.createdAt', $direction);
        }

        if ('VOTES' === $field) {
            $qb->addOrderBy('a.votesCount', $direction);
        }

        if ($first) {
            $qb->setFirstResult($first);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return new Paginator($qb);
    }

    public function countByContributionAndType(Argumentable $contribution, ?int $type = null): int
    {
        $qb = $this->getByContributionQB($contribution);
        $qb->select('COUNT(a.id)');

        if (null !== $type) {
            $qb->andWhere('a.type = :type')->setParameter('type', $type);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get all trashed or unpublished arguments for project.
     */
    public function getTrashedByProject(Project $project)
    {
        return $this->createQueryBuilder('a')
            ->addSelect('o', 'ov', 'v', 'aut', 'm')
            ->leftJoin('a.votes', 'v')
            ->leftJoin('a.Author', 'aut')
            ->leftJoin('aut.media', 'm')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('a.opinionVersion', 'ov')
            ->leftJoin('ov.parent', 'ovo')
            ->leftJoin('o.step', 'os')
            ->leftJoin('ovo.step', 'ovos')
            ->leftJoin('ovos.projectAbstractStep', 'ovopas')
            ->leftJoin('os.projectAbstractStep', 'opas')
            ->andWhere('opas.project = :project OR ovopas.project = :project')
            ->andWhere('a.trashedAt IS NOT NULL')
            ->setParameter('project', $project)
            ->orderBy('a.trashedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countAllByAuthor(User $user): int
    {
        $qb = $this->createQueryBuilder('version');
        $qb
            ->select('count(DISTINCT version)')
            ->andWhere('version.Author = :author')
            ->setParameter('author', $user);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findAllByAuthor(User $user): array
    {
        $qb = $this->createQueryBuilder('version');
        $qb->andWhere('version.Author = :author')->setParameter('author', $user);

        return $qb->getQuery()->getResult();
    }

    /**
     * Count all arguments by user.
     */
    public function countByUser(User $user): int
    {
        $qb /**
         * Project has no field or association named isEnabled
         */ = // ->andWhere('c.isEnabled = true')
        $this->getIsEnabledQueryBuilder()
            ->select('COUNT(a) as TotalArguments')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('o.step', 's')
            ->leftJoin('s.projectAbstractStep', 'cas')
            ->leftJoin('cas.project', 'c')
            ->andWhere('a.Author = :author')
            ->andWhere('o.published = true')
            ->andWhere('s.isEnabled = true')
            ->andWhere('a.trashedStatus <> :status OR a.trashedStatus IS NULL')
            ->setParameter('status', Trashable::STATUS_INVISIBLE)
            ->setParameter('author', $user);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function countByAuthorAndProject(User $author, Project $project): int
    {
        return $this->getIsEnabledQueryBuilder()
            ->select('COUNT(DISTINCT a)')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('a.opinionVersion', 'ov')
            ->leftJoin('ov.parent', 'ovo')
            ->andWhere('o.step IN (:steps) OR ovo.step IN (:steps)')
            ->andWhere('a.Author = :author')
            ->setParameter(
                'steps',
                array_map(function ($step) {
                    return $step;
                }, $project->getRealSteps())
            )
            ->setParameter('author', $author)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByAuthorAndStep(User $author, ConsultationStep $step): int
    {
        return $this->getIsEnabledQueryBuilder()
            ->select('COUNT(DISTINCT a)')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('a.opinionVersion', 'ov')
            ->leftJoin('ov.parent', 'ovo')
            ->andWhere('o.step = :step OR ovo.step = :step')
            ->andWhere('a.Author = :author')
            ->setParameter('step', $step)
            ->setParameter('author', $author)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get all arguments by user.
     */
    public function getByUser(User $user, int $first = 0, int $offset = 100): Paginator
    {
        $query = $this->getIsEnabledQueryBuilder();
        $query
            ->andWhere('a.Author = :author')
            ->andWhere('a.trashedStatus <> :status OR a.trashedStatus IS NULL')
            ->setParameter('author', $user)
            ->setParameter('status', Trashable::STATUS_INVISIBLE)
            ->setMaxResults($offset)
            ->setFirstResult($first);

        return new Paginator($query);
    }

    protected function getIsEnabledQueryBuilder()
    {
        return $this->createQueryBuilder('a')->andWhere('a.published = true');
    }

    private function getByContributionQB(Argumentable $contribution)
    {
        $qb = $this->getIsEnabledQueryBuilder()->andWhere('a.trashedAt IS NULL');
        if ($contribution instanceof Opinion) {
            $qb->andWhere('a.opinion = :opinion')->setParameter('opinion', $contribution);
        }
        if ($contribution instanceof OpinionVersion) {
            $qb
                ->andWhere('a.opinionVersion = :opinionVersion')
                ->setParameter('opinionVersion', $contribution);
        }

        return $qb;
    }

    protected function countPublishedBetweenByOpinion(
        \DateTime $from,
        \DateTime $to,
        string $opinionId,
        int $type
    ): int {
        $qb = $this->getIsEnabledQueryBuilder();
        $qb
            ->select('COUNT(a.id)')
            ->andWhere($qb->expr()->between('a.publishedAt', ':from', ':to'))
            ->andWhere('a.opinion = :id')
            ->andWhere('a.type = :type')
            ->orderBy('a.publishedAt', 'DESC')
            ->setParameters([
                'from' => $from,
                'to' => $to,
                'id' => $opinionId,
                'type' => $type,
            ]);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countAgainstPublishedBetweenByOpinion(
        \DateTime $from,
        \DateTime $to,
        string $opinionId
    ): int {
        return $this->countPublishedBetweenByOpinion(
            $from,
            $to,
            $opinionId,
            Argument::TYPE_AGAINST
        );
    }

    public function countForPublishedBetweenByOpinion(
        \DateTime $from,
        \DateTime $to,
        string $opinionId
    ): int {
        return $this->countPublishedBetweenByOpinion($from, $to, $opinionId, Argument::TYPE_FOR);
    }
}
