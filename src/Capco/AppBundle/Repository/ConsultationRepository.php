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
    public function findEnabledByTheme($theme)
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.Media', 'm')
            ->addSelect('m')
            ->leftJoin('c.Themes' , 't')
            ->addSelect('t')
            ->andWhere(':theme MEMBER OF c.Themes')
            ->setParameter('theme', $theme)
            ->orderBy('c.createdAt', 'DESC');

        $qb = $this->whereIsEnabled($qb);

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

    public function getLast($limit = 1, $offset = 0)
    {

        $qb = $this->getQBAll();

        $qb = $this->whereIsEnabled($qb);

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return new Paginator($qb, $fetchJoin = true);
    }


    public function getLastOpen($limit = 1, $offset = 0)
    {
        $result = $this->getLast($limit, $offset);

        return $this->getOpenedOnly($result);
    }

    public function getSearchResultsWithTheme($nbByPage = 8, $page = 1, $theme = null, $sort = null, $term = null)
    {
        if ((int) $page < 1) {
            throw new \InvalidArgumentException(sprintf(
                    'The argument "page" cannot be lower than 1 (current value: "%s")',
                    $page
                ));
        }

        $qb = $this->getIsEnabledQueryBuilder()
            ->leftJoin('c.Themes', 't')
            ->addSelect('t')
            ->leftJoin('c.Steps', 's')
            ->addSelect('s')
            ->addOrderBy('c.createdAt', 'DESC')
        ;

        $qb = $this->whereIsEnabled($qb);

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
            $qb->orderBy('c.contributionCount', 'DESC');
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

    public function getFirstResultWithMedia($slug)
    {
        $qb = $this->getIsEnabledQueryBuilder('c')
            ->leftJoin('c.Media', 'm')
            ->addSelect('m')
            ->leftJoin('c.Opinions', 'o')
            ->addSelect('o')
            ->addOrderBy('o.createdAt', 'DESC')
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function getQBAll(){

        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.Themes', 't')
            ->addSelect('t')
            ->leftJoin('c.Steps', 's')
            ->addSelect('s')
            ->addOrderBy('c.createdAt', 'DESC')
        ;

        return $qb;
    }



    private function whereIsEnabled(QueryBuilder $qb)
    {
        $qb->andWhere('c.isEnabled = :enabled')
            ->setParameter('enabled', true);
        return $qb;
    }

    private function getOpenedOnly($array){
        $result = array();
        foreach ($array as $c) {
            if($c->getOpeningStatus() == Consultation::OPENING_STATUS_OPENED){
                array_push($result, $c);
            }
        }
        return $result;
    }
}
