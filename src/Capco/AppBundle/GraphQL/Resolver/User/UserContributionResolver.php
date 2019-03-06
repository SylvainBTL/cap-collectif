<?php

namespace Capco\AppBundle\GraphQL\Resolver\User;

use Capco\AppBundle\Repository\ArgumentRepository;
use Capco\AppBundle\Repository\CommentRepository;
use Capco\AppBundle\Repository\OpinionRepository;
use Capco\AppBundle\Repository\OpinionVersionRepository;
use Capco\AppBundle\Repository\ProposalRepository;
use Capco\AppBundle\Repository\ReplyRepository;
use Capco\AppBundle\Repository\SourceRepository;
use Capco\UserBundle\Entity\User;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UserContributionResolver implements ContainerAwareInterface, ResolverInterface
{
    use ContainerAwareTrait;

    public function __invoke(User $object, Argument $args): Connection
    {
        $query = $this->getContributionsByType($args->offsetGet('type'), $object);
        $paginator = new Paginator(function (int $offset, int $limit) use ($query) {
            return $query['values'];
        });

        return $paginator->auto($args, $query['totalCount']);
    }

    public function getContributionsByType(string $requestedType = null, User $user): array
    {
        $result = [];

        switch ($requestedType) {
            case 'OPINION':
                $result['values'] = $this->container
                    ->get(OpinionRepository::class)
                    ->findAllByAuthor($user);
                $result['totalCount'] = $this->container
                    ->get(OpinionRepository::class)
                    ->countAllByAuthor($user);

                return $result;

                break;
            case 'OPINIONVERSION':
                $result['values'] = $this->container
                    ->get(OpinionVersionRepository::class)
                    ->findAllByAuthor($user);
                $result['totalCount'] = $this->container
                    ->get(OpinionVersionRepository::class)
                    ->countAllByAuthor($user);

                return $result;

                break;
            case 'COMMENT':
                $result['values'] = $this->container
                    ->get(CommentRepository::class)
                    ->findAllByAuthor($user);
                $result['totalCount'] = $this->container
                    ->get(CommentRepository::class)
                    ->countAllByAuthor($user);

                return $result;

                break;
            case 'ARGUMENT':
                $result['values'] = $this->container
                    ->get(ArgumentRepository::class)
                    ->findAllByAuthor($user);
                $result['totalCount'] = $this->container
                    ->get(ArgumentRepository::class)
                    ->countAllByAuthor($user);

                return $result;

                break;
            case 'SOURCE':
                $result['values'] = $this->container
                    ->get(SourceRepository::class)
                    ->findAllByAuthor($user);
                $result['totalCount'] = $this->container
                    ->get(SourceRepository::class)
                    ->countAllByAuthor($user);

                return $result;

                break;
            case 'PROPOSAL':
                $result['values'] = $this->container
                    ->get(ProposalRepository::class)
                    ->findAllByAuthor($user);
                $result['totalCount'] = $this->container
                    ->get(ProposalRepository::class)
                    ->countAllByAuthor($user);

                return $result;

                break;
            case 'REPLY':
                $result['values'] = $this->container
                    ->get(ReplyRepository::class)
                    ->findAllByAuthor($user);
                $result['totalCount'] = $this->container
                    ->get(ReplyRepository::class)
                    ->countAllByAuthor($user);

                return $result;

                break;
        }
    }
}
