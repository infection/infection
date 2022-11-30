<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
final class TestLocations
{
    public function __construct(private array $byLine = [], private array $byMethod = [])
    {
    }
    public function &getTestsLocationsBySourceLine() : array
    {
        return $this->byLine;
    }
    public function getSourceMethodRangeByMethod() : array
    {
        return $this->byMethod;
    }
}
