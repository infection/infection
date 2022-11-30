<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Schema;

use _HumbugBox9658796bb9f0\Infection\FileSystem\Locator\Locator;
final class SchemaConfigurationLoader
{
    public const POSSIBLE_DEFAULT_CONFIG_FILES = [self::DEFAULT_JSON5_CONFIG_FILE, self::DEFAULT_JSON_CONFIG_FILE, self::DEFAULT_DIST_JSON5_CONFIG_FILE, self::DEFAULT_DIST_JSON_CONFIG_FILE];
    public const DEFAULT_JSON5_CONFIG_FILE = 'infection.json5';
    private const DEFAULT_DIST_JSON5_CONFIG_FILE = 'infection.json5.dist';
    private const DEFAULT_DIST_JSON_CONFIG_FILE = 'infection.json.dist';
    private const DEFAULT_JSON_CONFIG_FILE = 'infection.json';
    private Locator $locator;
    private SchemaConfigurationFileLoader $fileLoader;
    public function __construct(Locator $locator, SchemaConfigurationFileLoader $fileLoader)
    {
        $this->locator = $locator;
        $this->fileLoader = $fileLoader;
    }
    public function loadConfiguration(array $potentialPaths) : SchemaConfiguration
    {
        return $this->fileLoader->loadFile($this->locator->locateOneOf($potentialPaths));
    }
}
