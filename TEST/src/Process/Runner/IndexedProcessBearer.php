<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Process\Runner;

final class IndexedProcessBearer
{
    public function __construct(public int $threadIndex, public ProcessBearer $processBearer)
    {
    }
}
