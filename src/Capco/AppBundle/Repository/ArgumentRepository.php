<?php

namespace Capco\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ArgumentRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArgumentRepository extends EntityRepository
{
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
    public function getByTypeAndOpinionOrderedJoinUserReports($type, $opinion, $argumentSort = null, $user = null)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('aut', 'm', 'v', 'r')
            ->leftJoin('a.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->leftJoin('a.votes', 'v')
            ->leftJoin('a.Reports', 'r', 'WITH', 'r.Reporter =  :user')
            ->andWhere('a.isTrashed = :notTrashed')
            ->andWhere('a.opinion = :opinion')
            ->andWhere('a.type = :type')
            ->setParameter('notTrashed', false)
            ->setParameter('opinion', $opinion)
            ->setParameter('type', $type)
            ->setParameter('user', $user);

        if (null != $argumentSort) {
            if ($argumentSort == 'popularity') {
                $qb->orderBy('a.voteCount', 'DESC');
            } elseif ($argumentSort == 'date') {
                $qb->orderBy('a.updatedAt', 'DESC');
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
            ->addSelect('o', 'ot', 'aut')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('o.OpinionType', 'ot')
            ->leftJoin('o.Author', 'aut')
            ->andWhere('o.isEnabled = :oEnabled')
            ->andWhere('o.step = :step')
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
            ->andWhere('a.id = :argument')
            ->setParameter('argument', $argument)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get all trashed arguments for consultation step.
     *
     * @param $step
     *
     * @return mixed
     */
    public function getTrashedByConsultationStep($step)
    {
        return $this->getIsEnabledQueryBuilder()
            ->addSelect('o', 'v', 'aut', 'm')
            ->leftJoin('a.opinion', 'o')
            ->leftJoin('a.votes', 'v')
            ->leftJoin('o.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->andWhere('a.isTrashed = :trashed')
            ->andWhere('o.step = :step')
            ->setParameter('trashed', true)
            ->setParameter('step', $step)
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
            ->leftJoin('s.consultationAbstractStep', 'cas')
            ->leftJoin('cas.consultation', 'c')
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
            ->leftJoin('s.consultationAbstractStep', 'cas')
            ->addSelect('cas')
            ->leftJoin('cas.consultation', 'c')
            ->addSelect('c')
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
            ->andWhere('c.isEnabled = :consultEnabled')
            ->setParameter('consultEnabled', true)
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
