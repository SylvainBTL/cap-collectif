<?php

namespace Capco\AppBundle\GraphQL\Resolver\Requirement;

use Capco\AppBundle\Entity\Requirement;
use GraphQL\Type\Definition\Type;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Capco\AppBundle\GraphQL\Resolver\TypeResolver;

class RequirementTypeResolver implements ResolverInterface
{
    private TypeResolver $typeResolver;

    public function __construct(TypeResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }

    public function __invoke(Requirement $requirement): Type
    {
        if (Requirement::CHECKBOX === $requirement->getType()) {
            return $this->typeResolver->resolve('CheckboxRequirement');
        }
        if (Requirement::FIRSTNAME === $requirement->getType()) {
            return $this->typeResolver->resolve('FirstnameRequirement');
        }
        if (Requirement::LASTNAME === $requirement->getType()) {
            return $this->typeResolver->resolve('LastnameRequirement');
        }
        if (Requirement::PHONE === $requirement->getType()) {
            return $this->typeResolver->resolve('PhoneRequirement');
        }
        if (Requirement::DATE_OF_BIRTH === $requirement->getType()) {
            return $this->typeResolver->resolve('DateOfBirthRequirement');
        }
        if (Requirement::POSTAL_ADDRESS === $requirement->getType()) {
            return $this->typeResolver->resolve('PostalAddressRequirement');
        }
        if (Requirement::IDENTIFICATION_CODE === $requirement->getType()) {
            return $this->typeResolver->resolve('IdentificationCodeRequirement');
        }
        if (Requirement::PHONE_VERIFIED === $requirement->getType()) {
            return $this->typeResolver->resolve('PhoneVerifiedRequirement');
        }
        if (Requirement::FRANCE_CONNECT === $requirement->getType()) {
            return $this->typeResolver->resolve('FranceConnectRequirement');
        }

        throw new UserError('Could not resolve type of Requirement.');
    }
}
