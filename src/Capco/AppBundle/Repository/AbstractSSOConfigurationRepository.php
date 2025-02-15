<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\SSO\AbstractSSOConfiguration;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method AbstractSSOConfiguration|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractSSOConfiguration|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractSSOConfiguration[]    findAll()
 * @method AbstractSSOConfiguration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) */
class AbstractSSOConfigurationRepository extends EntityRepository
{
    public function getPaginated(int $limit, int $offset): Paginator
    {
        $qb = $this->createQueryBuilder('sso')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return new Paginator($qb);
    }

    public function findSsoByType(int $limit, int $offset, string $type): Paginator
    {
        $qb = $this->createQueryBuilder('sso')
            ->andWhere('sso INSTANCE OF :type')
            ->setParameter('type', $type)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return new Paginator($qb);
    }

    public function findASsoByType(string $type): ?AbstractSSOConfiguration
    {
        $qb = $this->createQueryBuilder('sso')
            ->andWhere('sso INSTANCE OF :type')
            ->setParameter('type', $type);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getPublicList(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('ssoType', 'ssoType');
        $query = $this->getEntityManager()->createNativeQuery(
            '
            SELECT name, ssoType
            FROM sso_configuration
            WHERE enabled = 1
        ',
            $rsm
        );

        return $query->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function findOneActiveByType(string $type): ?AbstractSSOConfiguration
    {
        return $this->createQueryBuilder('sso')
            ->andWhere('sso INSTANCE OF :type')
            ->andWhere('sso.enabled = true')
            ->setParameters(['type' => $type])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
