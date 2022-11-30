<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\SebastianBergmann\Diff\Output;

interface DiffOutputBuilderInterface
{
    public function getDiff(array $diff) : string;
}
