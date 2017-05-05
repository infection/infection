<?php

declare(strict_types=1);


namespace Infection\Tests\Utils;


use Infection\Utils\InfectionConfig;
use PHPUnit\Framework\TestCase;

class InfectionConfigTest extends TestCase
{
    public function test_it_returns_default_timeout_with_no_config()
    {
        $config = new InfectionConfig(new \stdClass());

        $this->assertSame(InfectionConfig::PROCESS_TIMEOUT_SECONDS, $config->getProcessTimeout());
    }

    public function test_it_returns_timeout_from_config()
    {
        $timeout = 3;
        $json = sprintf('{"timeout": %d}', $timeout);
        $config = new InfectionConfig(json_decode($json));

        $this->assertSame($timeout, $config->getProcessTimeout());
    }

    public function test_it_returns_default_phpunit_config_dir_with_no_config()
    {
        $config = new InfectionConfig(new \stdClass());

        $this->assertSame(getcwd(), $config->getPhpUnitConfigDir());
    }

    public function test_it_returns_phpunit_config_dir_from_config()
    {
        $phpUnitConfigDir = 'app';
        $json = sprintf('{"phpUnit": {"configDir": "%s"}}', $phpUnitConfigDir);
        $config = new InfectionConfig(json_decode($json));

        $expected = getcwd() . '/app';

        $this->assertSame($expected, $config->getPhpUnitConfigDir());
    }
}