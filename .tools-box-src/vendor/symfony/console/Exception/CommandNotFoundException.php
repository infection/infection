<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception;

class CommandNotFoundException extends \InvalidArgumentException implements ExceptionInterface
{
    private array $alternatives;
    public function __construct(string $message, array $alternatives = [], int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->alternatives = $alternatives;
    }
    public function getAlternatives() : array
    {
        return $this->alternatives;
    }
}
