<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Config;

use Infection\Config\InfectionConfig;
use PHPUnit\Framework\TestCase;
use function Infection\Tests\normalizePath as p;

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

        $this->assertSame(p($expected), p($config->getPhpUnitConfigDir()));
    }

    public function test_it_returns_default_source_dirs_with_no_config()
    {
        $config = new InfectionConfig(new \stdClass());

        $this->assertSame(InfectionConfig::DEFAULT_SOURCE_DIRS, $config->getSourceDirs());
    }

    public function test_it_returns_source_dirs_from_config()
    {
        $excludedFolders = '["source-folder"]';
        $json = sprintf('{"source": {"directories": %s}}', $excludedFolders);
        $config = new InfectionConfig(json_decode($json));

        $this->assertSame(['source-folder'], $config->getSourceDirs());
    }

    public function test_it_returns_default_exclude_dirs_with_no_config()
    {
        $config = new InfectionConfig(new \stdClass());

        $this->assertSame(InfectionConfig::DEFAULT_EXCLUDE_DIRS, $config->getSourceExcludePaths());
    }

    public function test_it_returns_exclude_dirs_from_config_with_exclude_option()
    {
        $json = '{"source": {"exclude":["subfolder/excluded-folder"], "directories": ["source"]}}';
        $config = new InfectionConfig(json_decode($json));

        $this->assertSame(['subfolder/excluded-folder'], $config->getSourceExcludePaths());
    }

    public function test_it_returns_exclude_dirs_from_config_with_excludes_option()
    {
        $json = '{"source": {"excludes":["subfolder/excluded-folder"], "directories": ["source"]}}';
        $config = new InfectionConfig(json_decode($json));

        $this->assertSame(['subfolder/excluded-folder'], $config->getSourceExcludePaths());
    }

    public function test_it_excludes_by_glob_patterns()
    {
        $srcDir = __DIR__ . '/../Fixtures/Files/phpunit/project-path';
        $json = sprintf('{"source": {"exclude":["exclude/exclude*"], "directories": ["%s"]}}', p($srcDir));

        $config = new InfectionConfig(json_decode($json));

        $excludedDirs = $config->getSourceExcludePaths();

        $this->assertCount(2, $excludedDirs);
    }

    public function test_it_returns_text_file_log_path_when_exist()
    {
        $path = 'test-log.txt';
        $json = sprintf('{"logs": {"text": "%s"}}', $path);
        $config = new InfectionConfig(json_decode($json));

        $this->assertSame($path, $config->getLogPathInfoFor('text'));
    }

    public function test_it_returns_an_empty_array_for_text_file_log_path_when_it_is_skipped()
    {
        $config = new InfectionConfig(json_decode('{}'));

        $this->assertEmpty($config->getLogPathInfoFor('text'));
    }

    public function test_it_returns_default_temp_dir()
    {
        $config = new InfectionConfig(json_decode('{}'));

        $this->assertSame(sys_get_temp_dir(), $config->getTmpDir());
    }

    public function test_it_returns_temp_dir_from_config()
    {
        $config = new InfectionConfig(json_decode('{"tmpDir": "/root/test"}'));

        $this->assertSame('/root/test', $config->getTmpDir());
    }
}
