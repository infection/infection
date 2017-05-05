<?php

declare(strict_types=1);

namespace Infection\Utils;


class InfectionConfig
{
    const PROCESS_TIMEOUT_SECONDS = 10;

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
}