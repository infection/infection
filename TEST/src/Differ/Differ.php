<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Differ;

use _HumbugBox9658796bb9f0\SebastianBergmann\Diff\Differ as BaseDiffer;
class Differ
{
    public function __construct(private BaseDiffer $differ)
    {
    }
    public function diff(string $from, string $to) : string
    {
        return $this->differ->diff($from, $to);
    }
}
