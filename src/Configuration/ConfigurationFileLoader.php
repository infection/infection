<?php

declare(strict_types=1);

namespace Infection\Configuration;

use Infection\Configuration\RawConfiguration\RawConfiguration;
use Infection\Configuration\Schema\SchemaValidator;

final class ConfigurationFileLoader
{
    private $schemaValidator;
    private $factory;

    public function __construct(SchemaValidator $schemaValidator, ConfigurationFactory $factory)
    {
        $this->schemaValidator = $schemaValidator;
        $this->factory = $factory;
    }

    public function loadFile(string $file): Configuration
    {
        $rawConfig = new RawConfiguration($file);

        $this->schemaValidator->validate($rawConfig);

        return $this->factory->create($rawConfig);
    }
}