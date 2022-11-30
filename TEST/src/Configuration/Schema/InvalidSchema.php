<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Schema;

use function array_filter;
use function array_map;
use function implode;
use const PHP_EOL;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use UnexpectedValueException;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class InvalidSchema extends UnexpectedValueException
{
    public static function create(SchemaConfigurationFile $config, array $errors) : self
    {
        Assert::allString($errors);
        $errors = array_filter(array_map('trim', $errors));
        return new self(sprintf('"%s" does not match the expected JSON schema%s', $config->getPath(), $errors === [] ? '.' : ':' . PHP_EOL . ' - ' . implode(PHP_EOL . ' - ', $errors)));
    }
}
