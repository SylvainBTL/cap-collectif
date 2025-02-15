<?php

namespace Capco\AppBundle\GraphQL\Resolver\SiteSettings;

use Capco\AppBundle\Entity\SiteSettings ;
use Capco\AppBundle\GraphQL\Resolver\Traits\ResolverTrait;
use Capco\AppBundle\Repository\SiteSettingsRepository ;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class SiteSettingsResolver implements ResolverInterface
{
    use ResolverTrait;

    private SiteSettingsRepository $repository;

    public function __construct(SiteSettingsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(): SiteSettings
    {
        return $this->repository->findAll()[0] ?? new SiteSettings();
    }
}
