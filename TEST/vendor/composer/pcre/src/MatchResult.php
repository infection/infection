<?php

namespace _HumbugBox9658796bb9f0\Composer\Pcre;

final class MatchResult
{
    /**
    @readonly
    */
    public $matches;
    /**
    @readonly
    */
    public $matched;
    public function __construct($count, array $matches)
    {
        $this->matches = $matches;
        $this->matched = (bool) $count;
    }
}
