<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\MultipleChoiceQuestionLogicJumpCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MultipleChoiceQuestionLogicJumpCondition|null find($id, $lockMode = null, $lockVersion = null)
 * @method MultipleChoiceQuestionLogicJumpCondition|null findOneBy(array $criteria, array $orderBy = null)
 * @method MultipleChoiceQuestionLogicJumpCondition[]    findAll()
 * @method MultipleChoiceQuestionLogicJumpCondition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MultipleChoiceQuestionLogicJumpConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MultipleChoiceQuestionLogicJumpCondition::class);
    }
}
