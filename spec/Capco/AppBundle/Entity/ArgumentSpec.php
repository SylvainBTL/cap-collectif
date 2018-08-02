<?php
namespace spec\Capco\AppBundle\Entity;

use PhpSpec\ObjectBehavior;
use Capco\AppBundle\Model\Publishable;

class ArgumentSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Capco\AppBundle\Entity\Argument');
    }

    function it_is_a_publishable()
    {
        $this->shouldImplement(Publishable::class);
    }
}
