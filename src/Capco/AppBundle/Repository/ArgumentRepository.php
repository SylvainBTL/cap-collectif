<?php

namespace Capco\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Capco\AppBundle\Entity\Opinion;

/**
 * ArgumentRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArgumentRepository extends EntityRepository
{
    public function getRecentOrdered()
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id', 'a.createdAt', 'a.updatedAt', 'aut.username as author', 'a.isEnabled as published', 'a.isTrashed as trashed', 'c.title as project')
            ->leftJoin('a.Author', 'aut')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('o.step', 's')
            ->leftJoin('s.projectAbstractStep', 'cas')
            ->leftJoin('cas.project', 'c')
            ->where('a.validated = :validated')
            ->setParameter('validated', false)
        ;

        return $qb->getQuery()
            ->getArrayResult()
        ;
    }

    public function getArrayById($id)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.id', 'a.createdAt', 'a.updatedAt', 'aut.username as author', 'a.isEnabled as published', 'a.isTrashed as trashed', 'a.body as body', 'c.title as project')
            ->leftJoin('a.Author', 'aut')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('o.step', 's')
            ->leftJoin('s.projectAbstractStep', 'cas')
            ->leftJoin('cas.project', 'c')
            ->where('a.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_ARRAY)
        ;
    }

    /**
     * Get all enabled arguments by type and opinion, sorted by argumentSort.
     */

    /**
     * @param $type
     * @param $opinion
     * @param null $argumentSort
     *
     * @return array
     */
    public function getByTypeAndOpinionOrderedJoinUserReports($opinion, $type = null, $argumentSort = null, $user = null)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('aut', 'm', 'v')
            ->leftJoin('a.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->leftJoin('a.votes', 'v')
            ->andWhere('a.isTrashed = :notTrashed')
            ->andWhere('a.opinion = :opinion')
            ->setParameter('notTrashed', false)
            ->setParameter('opinion', $opinion)
        ;

        if ($type !== null) {
            $qb
                ->andWhere('a.type = :type')
                ->setParameter('type', $type)
            ;
        }

        if ($user !== null) {
            $qb
                ->addSelect('r')
                ->leftJoin('a.Reports', 'r', 'WITH', 'r.Reporter =  :user')
                ->setParameter('user', $user)
            ;
        }

        if (null != $argumentSort) {
            if ($argumentSort == 'popular') {
                $qb->orderBy('a.voteCount', 'DESC');
            } elseif ($argumentSort == 'last') {
                $qb->orderBy('a.updatedAt', 'DESC');
            } elseif ($argumentSort == 'old') {
                $qb->orderBy('a.updatedAt', 'ASC');
            }
        }

        $qb->addOrderBy('a.updatedAt', 'DESC');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Get all enabled arguments by type and opinion version, sorted by argumentSort.
     */

    /**
     * @param $type
     * @param $opinion
     * @param null $argumentSort
     *
     * @return array
     */
    public function getByTypeAndOpinionVersionOrderedJoinUserReports($version, $type = null, $argumentSort = null, $user = null)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('aut', 'm', 'v')
            ->leftJoin('a.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->leftJoin('a.votes', 'v')
            ->andWhere('a.isTrashed = :notTrashed')
            ->andWhere('a.opinionVersion = :version')
            ->setParameter('notTrashed', false)
            ->setParameter('version', $version)
        ;

        if ($type !== null) {
            $qb
                ->andWhere('a.type = :type')
                ->setParameter('type', $type)
            ;
        }

        if ($user !== null) {
            $qb
                ->addSelect('r')
                ->leftJoin('a.Reports', 'r', 'WITH', 'r.Reporter =  :user')
                ->setParameter('user', $user)
            ;
        }

        if (null != $argumentSort) {
            if ($argumentSort == 'popular') {
                $qb->orderBy('a.voteCount', 'DESC');
            } elseif ($argumentSort == 'last') {
                $qb->orderBy('a.updatedAt', 'DESC');
            } elseif ($argumentSort == 'old') {
                $qb->orderBy('a.updatedAt', 'ASC');
            }
        }

        $qb->addOrderBy('a.updatedAt', 'DESC');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Find enabled arguments by consultation step.
     *
     * @param $step
     *
     * @return array
     */
    public function getEnabledByConsultationStep($step)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('o', 'ot', 'aut', 'votes', 'vauthor')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('a.opinionVersion', 'ov')
            ->leftJoin('ov.parent', 'ovo')
            ->leftJoin('o.OpinionType', 'ot')
            ->leftJoin('o.Author', 'aut')
            ->leftJoin('a.votes', 'votes')
            ->leftJoin('votes.user', 'vauthor')
            ->andWhere('o.isEnabled = :oEnabled')
            ->andWhere('o.step = :step')
            ->orWhere('ovo.step = :step')
            ->setParameter('oEnabled', true)
            ->setParameter('step', $step)
            ->addOrderBy('a.updatedAt', 'DESC');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Get one argument by id.
     *
     * @param $argument
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneById($argument)
    {
        return $this->getIsEnabledQueryBuilder()
            ->addSelect('aut', 'm', 'v', 'o')
            ->leftJoin('a.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->leftJoin('a.votes', 'v')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('a.opinionVersion', 'ov')
            ->andWhere('a.id = :argument')
            ->setParameter('argument', $argument)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get all trashed or unpublished arguments for project.
     *
     * @param $step
     *
     * @return mixed
     */
    public function getTrashedOrUnpublishedByProject($project)
    {
        return $this->createQueryBuilder('a')
            ->addSelect('o', 'v', 'aut', 'm')
            ->leftJoin('a.votes', 'v')
            ->leftJoin('a.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('o.step', 's')
            ->leftJoin('s.projectAbstractStep', 'cas')
            ->andWhere('cas.project = :project')
            ->andWhere('a.isTrashed = :trashed')
            ->orWhere('a.isEnabled = :disabled')
            ->setParameter('project', $project)
            ->setParameter('trashed', true)
            ->setParameter('disabled', false)
            ->orderBy('a.trashedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count all arguments by user.
     *
     * @param $user
     *
     * @return mixed
     */
    public function countByUser($user)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->select('COUNT(a) as TotalArguments')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('o.step', 's')
            ->leftJoin('s.projectAbstractStep', 'cas')
            ->leftJoin('cas.project', 'c')
            ->andWhere('a.Author = :author')
            ->andWhere('o.isEnabled = :enabled')
            ->andWhere('s.isEnabled = :enabled')
            ->andWhere('c.isEnabled = :enabled')
            ->setParameter('author', $user)
            ->setParameter('enabled', true);

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get all arguments by user.
     *
     * @param $user
     *
     * @return mixed
     */
    public function getByUser($user)
    {
        return $this->getIsEnabledQueryBuilder()
            ->leftJoin('a.opinion', 'o')
            ->addSelect('o')
            ->leftJoin('o.step', 's')
            ->addSelect('s')
            ->leftJoin('s.projectAbstractStep', 'cas')
            ->addSelect('cas')
            ->leftJoin('cas.project', 'p')
            ->addSelect('p')
            ->leftJoin('o.Author', 'aut')
            ->addSelect('aut')
            ->leftJoin('aut.Media', 'm')
            ->addSelect('m')
            ->leftJoin('a.votes', 'v')
            ->addSelect('v')
            ->andWhere('a.Author = :author')
            ->setParameter('author', $user)
            ->andWhere('o.isEnabled = :enabled')
            ->setParameter('enabled', true)
            ->andWhere('s.isEnabled = :stepEnabled')
            ->setParameter('stepEnabled', true)
            ->andWhere('p.isEnabled = :projectEnabled')
            ->setParameter('projectEnabled', true)
            ->getQuery()
            ->getResult();
    }

    protected function getIsEnabledQueryBuilder()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isEnabled = :isEnabled')
            ->setParameter('isEnabled', true);
    }
}
