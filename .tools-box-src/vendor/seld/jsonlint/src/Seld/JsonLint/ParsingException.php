<?php

namespace _HumbugBoxb47773b41c19\Seld\JsonLint;

class ParsingException extends \Exception
{
    protected $details;
    /**
    @phpstan-param
    */
    public function __construct($message, $details = array())
    {
        $this->details = $details;
        parent::__construct($message);
    }
    /**
    @phpstan-return
    */
    public function getDetails()
    {
        return $this->details;
    }
}
