<?php

namespace Capco\AppBundle\GraphQL\Resolver;

use Capco\AppBundle\Entity\Project;
use Capco\UserBundle\Entity\User;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserContributionByProjectResolver
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(User $user, Project $project, array $args): Connection
    {
        $paginator = new Paginator(function ($offset, $limit) {
            return [];
        });

        if (!$project->hasParticipativeStep()) {
            return $paginator->auto($args, 0);
        }

        $totalCount = 0;

        // Contributions
        $totalCount += $this->container->get('capco.opinion.repository')->countByAuthorAndProject($user, $project);
        $totalCount += $this->container->get('capco.opinion_version.repository')->countByAuthorAndProject($user, $project);
        $totalCount += $this->container->get('capco.argument.repository')->countByAuthorAndProject($user, $project);
        $totalCount += $this->container->get('capco.source.repository')->countByAuthorAndProject($user, $project);
        $totalCount += $this->container->get('capco.proposal.repository')->countByAuthorAndProject($user, $project);
        $totalCount += $this->container->get('capco.reply.repository')->countByAuthorAndProject($user, $project);

        // Votes
        $totalCount += $this->container->get('capco.opinion_vote.repository')->countByAuthorAndProject($user, $project);
        $totalCount += $this->container->get('capco.argument_vote.repository')->countByAuthorAndProject($user, $project);
        $totalCount += $this->container->get('capco.source_vote.repository')->countByAuthorAndProject($user, $project);
        $totalCount += $this->container->get('capco.opinion_version_vote.repository')->countByAuthorAndProject($user, $project);
        $totalCount += $this->container->get('capco.proposal_collect_vote.repository')->countByAuthorAndProject($user, $project);
        $totalCount += $this->container->get('capco.proposal_selection_vote.repository')->countByAuthorAndProject($user, $project);

        // Comments are not accounted

        return $paginator->auto($args, $totalCount);
    }
}
