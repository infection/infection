<?php

namespace spec\Namespace_;

use PhpSpec\ObjectBehavior;

class SourceClassSpec extends ObjectBehavior
{
    public function it_returns_hello()
    {
        $this->add(1,2)->shouldReturn(3);
    }

    public function it_returns_true()
    {
        $this->isTrue()->shouldReturn(true);
    }
}
