<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception;

class CommandNotFoundException extends \InvalidArgumentException implements ExceptionInterface
{
    private $alternatives;
    public function __construct(string $message, array $alternatives = [], int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->alternatives = $alternatives;
    }
    public function getAlternatives()
    {
        return $this->alternatives;
    }
}
