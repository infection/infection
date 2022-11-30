<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event;

final class MutationTestingWasStarted
{
    public function __construct(private int $mutationCount)
    {
    }
    public function getMutationCount() : int
    {
        return $this->mutationCount;
    }
}
