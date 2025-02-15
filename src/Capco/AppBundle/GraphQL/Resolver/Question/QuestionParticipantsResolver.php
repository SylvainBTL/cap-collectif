<?php

namespace Capco\AppBundle\GraphQL\Resolver\Question;

use Capco\AppBundle\Entity\Questions\AbstractQuestion;
use Capco\AppBundle\Search\ResponseSearch;
use Overblog\GraphQLBundle\Definition\Argument as Arg;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class QuestionParticipantsResolver implements ResolverInterface
{
    private ResponseSearch $responseSearch;

    public function __construct(ResponseSearch $responseSearch)
    {
        $this->responseSearch = $responseSearch;
    }

    public function __invoke(AbstractQuestion $question, Arg $args): ConnectionInterface
    {
        $totalCount = $this->responseSearch->countParticipantsByQuestion(
            $question,
            $args->offsetGet('withNotConfirmedUser')
        );

        $paginator = new Paginator(function () {
            return [];
        });

        return $paginator->auto($args, $totalCount);
    }
}
