<?php

namespace spec\ProvideExistingCoverage;

use PhpSpec\ObjectBehavior;

class SourceClassSpec extends ObjectBehavior
{
    public function it_adds_numbers(): void
    {
        file_put_contents(__DIR__ . '/../has_run', 'phpspec');
        $this->add(1, 2)->shouldReturn(3);
    }

    public function it_returns_true(): void
    {
        $this->isTrue()->shouldReturn(true);
    }
}
