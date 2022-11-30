<?php

namespace _HumbugBox9658796bb9f0\Composer\Pcre;

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
    public function __construct($count, array $matches)
    {
        $this->matches = $matches;
        $this->matched = (bool) $count;
    }
}
