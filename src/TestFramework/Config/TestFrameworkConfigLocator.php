<?php

declare(strict_types=1);


namespace Infection\TestFramework\Config;


class TestFrameworkConfigLocator
{
    /**
     * @var string
     */
    private $configDir;

    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
    }

    public function locate(string $testFrameworkName, string $customDir = null): string
    {
        $dir = $customDir ?: $this->configDir;

        $conf = sprintf('%s/%s.xml', $dir, $testFrameworkName);

        if (file_exists($conf)) {
            return realpath($conf);
        }

        if (file_exists($conf . '.dist')) {
            return realpath($conf . '.dist');
        }

        throw new \RuntimeException(sprintf('Unable to locate %s.xml(.dist) file.', $testFrameworkName));
    }
}
