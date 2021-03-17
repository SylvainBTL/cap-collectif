<?php
namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\LogicJump;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry as RegistryInterface;

/**
 * @method LogicJump|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogicJump|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogicJump[]    findAll()
 * @method LogicJump[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogicJumpRepository extends EntityRepository
{

}
