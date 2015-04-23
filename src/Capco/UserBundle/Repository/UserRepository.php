<?php

namespace Capco\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Capco\UserBundle\Entity\User;
use Capco\UserBundle\Entity\UserType;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Capco\AppBundle\Entity\Consultation;

/**
 * UserRepository.
 */
class UserRepository extends EntityRepository
{
    public function findConsultationSourceContributorsWithCount(Consultation $consultation)
    {
        $qb = $this->createQueryBuilder('u')
                   ->select('u.id', 'count(distinct sources) as sources_count')
                   ->leftJoin('u.sources', 'sources', 'WITH', 'sources.isEnabled = 1')
                   ->leftJoin('sources.Opinion', 'sources_opinion', 'WITH', 'sources_opinion.isEnabled = 1')
                   ->where('sources_opinion.Consultation = :consultation')
                   ->groupBy('u')
                   ->setParameter('consultation', $consultation)
                ;

        return $qb->getQuery()->getResult();
    }

    public function findConsultationArgumentContributorsWithCount(Consultation $consultation)
    {
        $qb = $this->createQueryBuilder('u')
                   ->select('u.id', 'count(distinct arguments) as arguments_count')
                   ->leftJoin('u.arguments', 'arguments', 'WITH', 'arguments.isEnabled = 1')
                   ->leftJoin('arguments.opinion', 'arguments_opinion', 'WITH', 'arguments_opinion.isEnabled = 1')
                   ->where('arguments_opinion.Consultation = :consultation')
                   ->groupBy('u')
                   ->setParameter('consultation', $consultation)
                ;

        return $qb->getQuery()->getResult();
    }

    public function findConsultationOpinionContributorsWithCount(Consultation $consultation)
    {
        $qb = $this->createQueryBuilder('u')
                    ->select('u.id', 'count(distinct opinions) as opinions_count')
                    ->leftJoin('u.opinions', 'opinions', 'WITH', 'opinions.isEnabled = 1')
                    ->where('opinions.Consultation = :consultation')
                    ->groupBy('u')
                    ->setParameter('consultation', $consultation)
            ;

        return $qb->getQuery()->getResult();
    }

    public function findConsultationOpinionVotersWithCount(Consultation $consultation)
    {
        $qb = $this->createQueryBuilder('u')
                    ->select('u.id', 'count(distinct opinions_votes) as opinions_votes_count')
                    ->leftJoin('CapcoAppBundle:OpinionVote', 'opinions_votes', 'WITH', 'opinions_votes.user = u AND opinions_votes.confirmed = 1')
                    ->leftJoin('opinions_votes.opinion', 'opinions_votes_opinion', 'WITH', 'opinions_votes_opinion.isEnabled = 1')
                    ->where('opinions_votes_opinion.Consultation = :consultation')
                    ->groupBy('u')
                    ->setParameter('consultation', $consultation)
            ;

        return $qb->getQuery()->getResult();
    }

    public function findConsultationArgumentVotersWithCount(Consultation $consultation)
    {
        $qb = $this->createQueryBuilder('u')
                    ->select('u.id', 'count(distinct arguments_votes) as arguments_votes_count')
                    ->leftJoin('CapcoAppBundle:ArgumentVote', 'arguments_votes', 'WITH', 'arguments_votes.user = u AND arguments_votes.confirmed = 1')
                    ->leftJoin('arguments_votes.argument', 'arguments_votes_argument', 'WITH', 'arguments_votes_argument.isEnabled = 1')
                    ->leftJoin('arguments_votes_argument.opinion', 'arguments_votes_argument_opinion', 'WITH', 'arguments_votes_argument_opinion.isEnabled = 1')
                    ->where('arguments_votes_argument_opinion.Consultation = :consultation')
                    ->groupBy('u')
                    ->setParameter('consultation', $consultation)
            ;

        return $qb->getQuery()->getResult();
    }

    public function findConsultationSourceVotersWithCount(Consultation $consultation)
    {
        $qb = $this->createQueryBuilder('u')
                    ->select('u.id', 'count(distinct sources_votes) as sources_votes_count')
                    ->leftJoin('CapcoAppBundle:SourceVote', 'sources_votes', 'WITH', 'sources_votes.user = u AND sources_votes.confirmed = 1')
                    ->leftJoin('sources_votes.source', 'sources_votes_source', 'WITH', 'sources_votes_source.isEnabled = 1')
                    ->leftJoin('sources_votes_source.Opinion', 'sources_votes_source_opinion', 'WITH', 'sources_votes_source_opinion.isEnabled = 1')
                    ->where('sources_votes_source_opinion.Consultation = :consultation')
                    ->groupBy('u')
                    ->setParameter('consultation', $consultation)
            ;

        return $qb->getQuery()->getResult();
    }

    public function findWithMediaByIds($ids)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->addSelect('m')
            ->leftJoin('u.Media', 'm')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get search results.
     *
     * @param int  $nbByPage
     * @param int  $page
     * @param null $sort
     * @param null $type
     *
     * @return Paginator
     */
    public function getSearchResults($nbByPage = 8, $page = 1, $sort = null, $type = null)
    {
        if ((int) $page < 1) {
            throw new \InvalidArgumentException(sprintf(
                'The argument "page" cannot be lower than 1 (current value: "%s")',
                $page
            ));
        }

        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('m', 'ut')
            ->leftJoin('u.Media', 'm')
            ->leftJoin('u.userType', 'ut')
            ->addOrderBy('u.createdAt', 'DESC')
        ;

        if ($type !== null && $type !== UserType::FILTER_ALL) {
            $qb->andWhere('ut.slug = :type')
                ->setParameter('type', $type)
            ;
        }

        if (isset(User::$sortOrder[$sort]) && User::$sortOrder[$sort] == User::SORT_ORDER_CONTRIBUTIONS_COUNT) {
            $qb = $this->orderByContributionsCount($qb, 'DESC');
            $qb->addOrderBy('u.createdAt', 'DESC');
        }

        $query = $qb->getQuery();

        if ($nbByPage > 0) {
            $query->setFirstResult(($page - 1) * $nbByPage)
                ->setMaxResults($nbByPage);
        }

        return new Paginator($query);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getIsEnabledQueryBuilder()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.enabled = :enabled')
            ->setParameter('enabled', true);
    }

    public function orderByContributionsCount(QueryBuilder $qb, $order = 'DESC')
    {
        return $qb->addSelect('(u.opinionsCount + u.argumentsCount + u.sourcesCount + u.ideasCount + u.ideaCommentsCount + u.postCommentsCount + u.eventCommentsCount + u.ideaVotesCount + u.commentVotesCount + u.opinionVotesCount + u.argumentVotesCount + u.sourceVotesCount) AS HIDDEN contributionsCount')
            ->orderBy('contributionsCount', $order)
        ;
    }
}
