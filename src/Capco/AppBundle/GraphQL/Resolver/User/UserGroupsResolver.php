<?php

namespace Capco\AppBundle\GraphQL\Resolver\User;

use Capco\UserBundle\Entity\User;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UserGroupsResolver implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __invoke(User $user, Argument $args): Connection
    {
        $paginator = new Paginator(function (int $offset, int $limit) use ($user) {
            return  $this->container->get('capco.group.repository')->getGroupsByUser($user);
        });

        $totalCount = $this->container->get('capco.user_group.repository')->countAllByUser($user);

        return $paginator->auto($args, $totalCount);
    }
}
