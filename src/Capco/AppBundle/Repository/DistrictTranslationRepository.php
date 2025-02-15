<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\District\DistrictTranslation;
use Doctrine\ORM\EntityRepository;

/**
 * @method DistrictTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method DistrictTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method DistrictTranslation[]    findAll()
 * @method DistrictTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DistrictTranslationRepository extends EntityRepository
{
}
