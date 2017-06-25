<?php

declare(strict_types=1);


namespace Infection\Tests\Config;


use Infection\Config\InfectionConfig;
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

        $this->assertSame(InfectionConfig::DEFAULT_EXCLUDE_DIRS, $config->getSourceExcludeDirs());
    }

    public function test_it_returns_exclude_dirs_from_config()
    {
        $json = '{"source": {"exclude":["subfolder/excluded-folder"], "directories": ["source"]}}';
        $config = new InfectionConfig(json_decode($json));

        $this->assertSame(['subfolder/excluded-folder'], $config->getSourceExcludeDirs());
    }

    public function test_it_excludes_by_glob_patterns()
    {
        $srcDir = __DIR__ . '/../Files/phpunit/project-path';
        $json = sprintf(
            '{"source": {"exclude":["exclude/exclude*"], "directories": ["%s"]}}',
            $srcDir
        );
        $config = new InfectionConfig(json_decode($json));

        $excludedDirs = $config->getSourceExcludeDirs();

        $this->assertCount(2, $excludedDirs);
    }

    public function test_it_returns_text_file_log_path_when_exist()
    {
        $path = 'test-log.txt';
        $json = sprintf('{"logs": {"text": "%s"}}', $path);
        $config = new InfectionConfig(json_decode($json));

        $this->assertSame($path, $config->getTextFileLogPath());
    }

    public function test_it_returns_null_for_text_file_log_path_when_it_is_skipped()
    {
        $config = new InfectionConfig(json_decode('{}'));

        $this->assertNull($config->getTextFileLogPath());
    }
}
