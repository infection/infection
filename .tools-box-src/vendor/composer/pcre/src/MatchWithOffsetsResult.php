<?php

namespace _HumbugBoxb47773b41c19\Composer\Pcre;

final class MatchWithOffsetsResult
{
    /**
    @readonly
    @phpstan-var
    */
    public $matches;
    /**
    @readonly
    */
    public $matched;
    /**
    @phpstan-param
    */
    public function __construct(int $count, array $matches)
    {
        $this->matches = $matches;
        $this->matched = (bool) $count;
    }
}
