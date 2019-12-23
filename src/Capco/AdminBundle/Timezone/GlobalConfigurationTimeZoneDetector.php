<?php

namespace Capco\AdminBundle\Timezone;

use Capco\AppBundle\SiteParameter\Resolver;
use Sonata\IntlBundle\Timezone\TimezoneDetectorInterface;

class GlobalConfigurationTimeZoneDetector implements TimezoneDetectorInterface
{
    protected $resolver;

    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function getTimezone(): string
    {
        return $this->resolver->getValue('global.timezone');
    }
}
