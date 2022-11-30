<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event;

final class MutationGenerationWasStarted
{
    public function __construct(private int $mutableFilesCount)
    {
    }
    public function getMutableFilesCount() : int
    {
        return $this->mutableFilesCount;
    }
}
