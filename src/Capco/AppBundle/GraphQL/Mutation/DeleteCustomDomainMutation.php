<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Client\DeployerClient;
use Capco\AppBundle\Enum\SiteSettingsStatus;
use Capco\AppBundle\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class DeleteCustomDomainMutation implements MutationInterface
{
    public const ERROR_DEPLOYER_API = 'ERROR_DEPLOYER_API';

    private EntityManagerInterface $em;
    private SiteSettingsRepository $siteSettingsRepository;
    private DeployerClient $deployerClient;

    public function __construct(
        EntityManagerInterface $em,
        SiteSettingsRepository $siteSettingsRepository,
        DeployerClient $deployerClient
    ) {
        $this->em = $em;
        $this->siteSettingsRepository = $siteSettingsRepository;
        $this->deployerClient = $deployerClient;
    }

    public function __invoke(Argument $input): array
    {
        $siteSettings = $this->siteSettingsRepository->findSiteSetting();
        $capcoDomain = $siteSettings->getCapcoDomain();

        try {
            $statusCode = $this->deployerClient->updateCurrentDomain($capcoDomain);
        } catch (\Exception $e) {
            return ['siteSettings' => $siteSettings, 'errorCode' => self::ERROR_DEPLOYER_API];
        }

        if ($statusCode === 201) {
            $siteSettings->setCustomDomain(null);
            $siteSettings->setStatus(SiteSettingsStatus::IDLE);
            $this->em->flush();
            return ['siteSettings' => $siteSettings, 'errorCode' => null];
        }

        return ['siteSettings' => $siteSettings, 'errorCode' => self::ERROR_DEPLOYER_API];
    }
}
