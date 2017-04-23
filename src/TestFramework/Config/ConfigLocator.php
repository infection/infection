<?php

declare(strict_types=1);


namespace Infection\TestFramework\Config;


class ConfigLocator
{
    /**
     * @var string
     */
    private $configDir;


    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
    }

    public function locate()
    {
        $conf = $this->configDir . '/phpunit.xml';

        if (file_exists($conf)) {
            return realpath($conf);
        }

        if (file_exists($conf . '.dist')) {
            return realpath($conf . '.dist');
        }

        throw new \RuntimeException('Unable to locate phpunit.xml(.dist) file.');
    }
}
