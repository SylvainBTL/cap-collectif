<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\Consultation;
use Capco\AppBundle\Entity\Theme;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\Collection;

/**
 * ConsultationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ConsultationRepository extends EntityRepository
{

    /**
     * Get one by slug
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOne($slug)
    {
        $qb = $this->getIsEnabledQueryBuilder('c')
            ->addSelect('t', 's', 'cov', 'o')
            ->leftJoin('c.Themes', 't')
            ->leftJoin('c.Steps', 's')
            ->leftJoin('c.Cover', 'cov')
            ->leftJoin('c.Opinions', 'o')
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->addOrderBy('o.createdAt', 'DESC');

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get search results
     * @param int $nbByPage
     * @param int $page
     * @param null $theme
     * @param null $sort
     * @param null $term
     * @return Paginator
     */
    public function getSearchResults($nbByPage = 8, $page = 1, $theme = null, $sort = null, $term = null)
    {
        if ((int) $page < 1) {
            throw new \InvalidArgumentException(sprintf(
                'The argument "page" cannot be lower than 1 (current value: "%s")',
                $page
            ));
        }

        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('t', 's', 'cov')
            ->leftJoin('c.Themes', 't')
            ->leftJoin('c.Steps', 's')
            ->leftJoin('c.Cover', 'cov')
            ->addOrderBy('c.createdAt', 'DESC');

        if ($theme !== null && $theme !== Theme::FILTER_ALL) {
            $qb->andWhere('t.slug = :theme')
                ->setParameter('theme', $theme)
            ;
        }

        if ($term !== null) {
            $qb->andWhere('c.title LIKE :term')
                ->setParameter('term', '%'.$term.'%')
            ;
        }

        if (isset(Consultation::$sortOrder[$sort]) && Consultation::$sortOrder[$sort] == Consultation::SORT_ORDER_VOTES_COUNT) {
            $qb->orderBy('c.opinionCount', 'DESC');
        } else {
            $qb->orderBy('c.createdAt', 'DESC');
        }

        $query = $qb->getQuery();

        if($nbByPage > 0){
            $query->setFirstResult(($page - 1) * $nbByPage)
                ->setMaxResults($nbByPage);
        }

        return new Paginator($query);
    }

    /**
     * Get last consultations
     * @param int $limit
     * @param int $offset
     * @return Paginator
     */
    public function getLast($limit = 1, $offset = 0)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('t', 's', 'cov')
            ->leftJoin('c.Themes', 't')
            ->leftJoin('c.Steps', 's')
            ->leftJoin('c.Cover', 'cov')
            ->addOrderBy('c.updatedAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return new Paginator($qb, $fetchJoin = true);
    }

    /**
     * Get consultations by theme
     * @param theme
     * @return mixed
     */
    public function getByTheme($theme)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('cov', 't', 's')
            ->leftJoin('c.Cover', 'cov')
            ->leftJoin('c.Themes' , 't')
            ->leftJoin('c.Steps', 's')
            ->andWhere(':theme MEMBER OF c.Themes')
            ->setParameter('theme', $theme)
            ->orderBy('c.createdAt', 'DESC');

        return $qb
            ->getQuery()
            ->execute();
    }

    protected function getIsEnabledQueryBuilder()
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isEnabled = :isEnabled')
            ->setParameter('isEnabled', true);
    }

}
