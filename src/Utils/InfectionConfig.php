<?php

declare(strict_types=1);

namespace Infection\Utils;


class InfectionConfig
{
    const PROCESS_TIMEOUT_SECONDS = 10;
    const DEFAULT_SOURCE_DIRS = ['src'];
    const DEFAULT_EXCLUDE_DIRS = ['vendor'];

    /**
     * @var \stdClass
     */
    private $config;

    public function __construct(\stdClass $config)
    {
        $this->config = $config;
    }

    public function getPhpUnitConfigDir(): string
    {
        if (isset($this->config->phpUnit->configDir)) {
            return getcwd() . DIRECTORY_SEPARATOR . $this->config->phpUnit->configDir;
        }

        return getcwd();
    }

    public function getProcessTimeout(): int
    {
        return $this->config->timeout ?? self::PROCESS_TIMEOUT_SECONDS;
    }

    public function getSourceDirs()
    {
        return $this->config->source->directories ?? self::DEFAULT_SOURCE_DIRS;
    }

    public function getSourceExcludeDirs()
    {
        if (isset($this->config->source->exclude) && is_array($this->config->source->exclude)) {
            return array_map(
                function ($excludeDir) {
                    foreach ($this->getSourceDirs() as $sourceDir) {
                        if (strpos($excludeDir, $sourceDir) === 0) {
                            return ltrim(
                                substr_replace($excludeDir, '', 0, strlen($sourceDir)),
                                DIRECTORY_SEPARATOR
                            );
                        }
                    }

                    return $excludeDir;
                },
                $this->config->source->exclude
            );
        }

        return self::DEFAULT_EXCLUDE_DIRS;
    }
}