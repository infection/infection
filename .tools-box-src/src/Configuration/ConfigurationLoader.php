<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Configuration;

use _HumbugBoxb47773b41c19\KevinGH\Box\Json\Json;
use stdClass;
final class ConfigurationLoader
{
    private const SCHEMA_FILE = __DIR__ . '/../../res/schema.json';
    public function __construct(private readonly Json $json = new Json())
    {
    }
    public function loadFile(?string $file) : Configuration
    {
        if (null === $file) {
            return Configuration::create(null, new stdClass());
        }
        $json = $this->json->decodeFile($file);
        $this->json->validate($file, $json, $this->json->decodeFile(self::SCHEMA_FILE));
        return Configuration::create($file, $json);
    }
}
