<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Configuration;

use RuntimeException;
use Throwable;
final class NoConfigurationFound extends RuntimeException
{
    public function __construct(string $message = 'The configuration file could not be found.', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
