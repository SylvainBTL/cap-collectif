<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Entity\OfficialResponse;
use Capco\AppBundle\Enum\ErrorCode\DeleteOfficialResponseErrorCode;
use Capco\AppBundle\GraphQL\Resolver\GlobalIdResolver;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;

class DeleteOfficialResponseMutation implements MutationInterface
{
    private GlobalIdResolver $resolver;
    private EntityManagerInterface $em;

    public function __construct(GlobalIdResolver $resolver, EntityManagerInterface $em)
    {
        $this->resolver = $resolver;
        $this->em = $em;
    }

    public function __invoke(Argument $input, User $user): array
    {
        $id = $input->offsetGet('id');
        try {
            $officialResponse = $this->getOfficialResponse($id, $user);
            $this->em->remove($officialResponse);
        } catch (UserError $exception) {
            return ['error' => $exception->getMessage()];
        }

        return compact('id');
    }

    private function getOfficialResponse(string $id, User $user): OfficialResponse
    {
        $officialResponse = $this->resolver->resolve($id, $user);
        if (null === $officialResponse) {
            throw new UserError(DeleteOfficialResponseErrorCode::ID_NOT_FOUND);
        }

        return $officialResponse;
    }
}
