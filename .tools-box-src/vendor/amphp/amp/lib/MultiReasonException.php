<?php

namespace _HumbugBoxb47773b41c19\Amp;

class MultiReasonException extends \Exception
{
    private $reasons;
    public function __construct(array $reasons, string $message = null)
    {
        parent::__construct($message ?: "Multiple errors encountered; use " . self::class . "::getReasons() to retrieve the array of exceptions thrown");
        $this->reasons = $reasons;
    }
    public function getReasons() : array
    {
        return $this->reasons;
    }
}
