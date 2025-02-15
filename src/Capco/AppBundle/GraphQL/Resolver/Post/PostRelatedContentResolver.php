<?php

namespace Capco\AppBundle\GraphQL\Resolver\Post;

use Capco\AppBundle\Entity\Post;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class PostRelatedContentResolver implements ResolverInterface
{
    public function __invoke(Post $post): iterable
    {
        return array_merge(
            $post->getThemes()->toArray(),
            $post->getProposals()->toArray(),
            $post->getProjects()->toArray()
        );
    }
}
