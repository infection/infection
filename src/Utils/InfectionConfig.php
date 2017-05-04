<?php

declare(strict_types=1);

namespace Infection\Utils;


class InfectionConfig
{
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
        return sprintf('%s/%s', getcwd(), $this->config->phpUnit->configDir ?? '');
    }
}