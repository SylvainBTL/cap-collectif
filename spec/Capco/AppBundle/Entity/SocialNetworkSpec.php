<?php

namespace spec\Capco\AppBundle\Entity;

use PhpSpec\ObjectBehavior;

class SocialNetworkSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Capco\AppBundle\Entity\SocialNetwork');
    }
}
