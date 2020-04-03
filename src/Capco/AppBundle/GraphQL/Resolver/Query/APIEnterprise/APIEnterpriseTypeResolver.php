<?php

namespace Capco\AppBundle\GraphQL\Resolver\Query\APIEnterprise;

use Capco\AppBundle\GraphQL\Resolver\TypeResolver;
use GraphQL\Type\Definition\Type;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;

class APIEnterpriseTypeResolver implements ResolverInterface
{
    private $typeResolver;

    public const ENTERPRISE = 'APIEnterpriseEnterprise';
    public const ASSOCIATION = 'APIEnterpriseAssociation';
    public const PUBLIC_ORGA = 'APIEnterprisePublicOrganization';

    public function __construct(TypeResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }

    public function __invoke(array $data = []): ?Type
    {
        if (!isset($data['type'])){
            throw new UserError('Could not resolve type of APIEnterprise.');
        }
        switch ($data['type']) {
            case self::ENTERPRISE:
            case self::ASSOCIATION:
            case self::PUBLIC_ORGA:
                return $this->typeResolver->resolve($data['type']);
            default:
                throw new UserError('Could not resolve type of APIEnterprise.');
        }
    }
}
