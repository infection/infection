<?php

namespace _HumbugBoxb47773b41c19\Composer\Pcre;

final class MatchStrictGroupsResult
{
    /**
    @readonly
    */
    public $matches;
    /**
    @readonly
    */
    public $matched;
    public function __construct(int $count, array $matches)
    {
        $this->matches = $matches;
        $this->matched = (bool) $count;
    }
}
