<?php

namespace _HumbugBox9658796bb9f0\Composer\Pcre;

final class MatchAllWithOffsetsResult
{
    /**
    @readonly
    @phpstan-var
    */
    public $matches;
    /**
    @readonly
    */
    public $count;
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
        $this->count = $count;
    }
}
