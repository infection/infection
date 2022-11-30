<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage;

use function range;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class NodeLineRangeData
{
    public array $range;
    public function __construct(int $start, int $end)
    {
        Assert::greaterThanEq($end, $start);
        $this->range = range($start, $end);
    }
}
