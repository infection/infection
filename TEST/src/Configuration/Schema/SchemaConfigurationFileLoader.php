<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Schema;

class SchemaConfigurationFileLoader
{
    private SchemaValidator $schemaValidator;
    private SchemaConfigurationFactory $factory;
    public function __construct(SchemaValidator $schemaValidator, SchemaConfigurationFactory $factory)
    {
        $this->schemaValidator = $schemaValidator;
        $this->factory = $factory;
    }
    public function loadFile(string $file) : SchemaConfiguration
    {
        $rawConfig = new SchemaConfigurationFile($file);
        $this->schemaValidator->validate($rawConfig);
        return $this->factory->create($rawConfig->getPath(), $rawConfig->getDecodedContents());
    }
}
