<?php

namespace _HumbugBox9658796bb9f0\Composer\Pcre;

final class ReplaceResult
{
    /**
    @readonly
    */
    public $result;
    /**
    @readonly
    */
    public $count;
    /**
    @readonly
    */
    public $matched;
    public function __construct($count, $result)
    {
        $this->count = $count;
        $this->matched = (bool) $count;
        $this->result = $result;
    }
}
