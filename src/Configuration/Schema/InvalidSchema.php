<?php

declare(strict_types=1);

namespace Infection\Configuration\Schema;

use Infection\Configuration\RawConfiguration\RawConfiguration;
use UnexpectedValueException;
use Webmozart\Assert\Assert;
use function array_filter;
use function array_map;
use function sprintf;

final class InvalidSchema extends UnexpectedValueException
{
    /**
     * @param string[]
     */
    public static function create(RawConfiguration $config, array $errors): self
    {
        Assert::allString($errors);

        $errors = array_filter(array_map('trim', $errors));

        return new self(sprintf(
            '"%s" does not match the expected JSON schema%s',
            $config->getPath(),
            [] === $errors
                ? '.'
                : ':'.PHP_EOL.' - '.implode(PHP_EOL.' - ', $errors)
        ));
    }
}