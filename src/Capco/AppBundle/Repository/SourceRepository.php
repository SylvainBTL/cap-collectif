<?php

namespace Capco\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * SourceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SourceRepository extends EntityRepository
{
    /**
     * Get one source by slug, opinion, opinion type and consultation
     * @param $consultation
     * @param $opinionType
     * @param $opinion
     * @param $source
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneBySlug($consultation, $opinionType, $opinion, $source)
    {
        return $this->getIsEnabledQueryBuilder()
            ->addSelect('a', 'm', 'v', 'o', 'c', 'ot', 'cat', 'media')
            ->leftJoin('s.Author', 'a')
            ->leftJoin('s.Media', 'media')
            ->leftJoin('s.Category', 'cat')
            ->leftJoin('a.Media', 'm')
            ->leftJoin('s.Votes', 'v')
            ->leftJoin('s.Opinion', 'o')
            ->leftJoin('o.Consultation', 'c')
            ->leftJoin('o.OpinionType', 'ot')
            ->andWhere('s.slug = :source')
            ->andWhere('o.slug = :opinion')
            ->andWhere('c.slug = :consultation')
            ->andWhere('ot.slug = :opinionType')
            ->setParameter('source', $source)
            ->setParameter('opinion', $opinion)
            ->setParameter('consultation', $consultation)
            ->setParameter('opinionType', $opinionType)

            ->getQuery()
            ->getOneOrNullResult();

    }

    /**
     * Get all trashed sources for consultation
     * @param $consultation
     * @return mixed
     */
    public function getTrashedByConsultation($consultation)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('ca', 'o', 'aut', 'm', 'media')
            ->leftJoin('s.Category', 'ca')
            ->leftJoin('s.Media', 'media')
            ->leftJoin('s.Opinion', 'o')
            ->leftJoin('s.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->andWhere('o.Consultation = :consultation')
            ->andWhere('s.isTrashed = :trashed')
            ->setParameter('consultation', $consultation)
            ->setParameter('trashed', true)
            ->orderBy('s.trashedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get sources by opinion
     * @param $opinion
     * @return mixed
     */
    public function getByOpinion($opinion)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('ca', 'o', 'aut', 'm', 'media')
            ->leftJoin('s.Category', 'ca')
            ->leftJoin('s.Media', 'media')
            ->leftJoin('s.Opinion', 'o')
            ->leftJoin('s.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->andWhere('s.isTrashed = :notTrashed')
            ->andWhere('s.Opinion = :opinion')
            ->setParameter('notTrashed', false)
            ->setParameter('opinion', $opinion)
            ->orderBy('s.updatedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get sources by user
     * @param $user
     * @return mixed
     */
    public function getByUser($user)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('ca', 'o', 'c', 'aut', 'm', 'media')
            ->Join('s.Category', 'ca')
            ->Join('s.media', 'media')
            ->Join('s.Opinion', 'o')
            ->Join('o.Consultation', 'c')
            ->Join('s.Author', 'aut')
            ->Join('aut.Media', 'm')
            ->andWhere('s.Author = :author')
            ->andWhere('o.isEnabled = :enabled')
            ->andWhere('c.isEnabled = :enabled')
            ->setParameter('author', $user)
            ->setParameter('enabled', true)
            ->orderBy('s.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Count by user
     * @param $user
     * @return mixed
     */
    public function countByUser($user)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->select('COUNT(s) as TotalSources')
            ->leftJoin('s.Opinion', 'o')
            ->leftJoin('o.Consultation', 'c')
            ->andWhere('o.isEnabled = :enabled')
            ->andWhere('c.isEnabled = :enabled')
            ->andWhere('s.Author = :author')
            ->setParameter('enabled', true)
            ->setParameter('author', $user);

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    protected function getIsEnabledQueryBuilder()
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isEnabled = :isEnabled')
            ->setParameter('isEnabled', true);
    }
}
