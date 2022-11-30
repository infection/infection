<?php

namespace _HumbugBoxb47773b41c19\Seld\JsonLint;

class DuplicateKeyException extends ParsingException
{
    /**
    @phpstan-ignore-next-line
    */
    protected $details;
    /**
    @phpstan-param
    */
    public function __construct($message, $key, array $details)
    {
        $details['key'] = $key;
        parent::__construct($message, $details);
    }
    public function getKey()
    {
        return $this->details['key'];
    }
    /**
    @phpstan-return
    */
    public function getDetails()
    {
        return $this->details;
    }
}
