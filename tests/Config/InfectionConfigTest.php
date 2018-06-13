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
use Symfony\Component\Filesystem\Filesystem;
use function Infection\Tests\normalizePath as p;

/**
 * @internal
 */
final class InfectionConfigTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    protected function setUp()
    {
        $this->filesystem = new Filesystem();
    }

    public function test_it_returns_default_timeout_with_no_config()
    {
        $config = new InfectionConfig(new \stdClass(), $this->filesystem, '/path/to/config');

        $this->assertSame(InfectionConfig::PROCESS_TIMEOUT_SECONDS, $config->getProcessTimeout());
    }

    public function test_it_returns_timeout_from_config()
    {
        $timeout = 3;
        $json = sprintf('{"timeout": %d}', $timeout);
        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $this->assertSame($timeout, $config->getProcessTimeout());
    }

    public function test_it_returns_default_phpunit_config_dir_with_no_config()
    {
        $config = new InfectionConfig(new \stdClass(), $this->filesystem, '/path/to/config');

        $this->assertSame('/path/to/config', $config->getPhpUnitConfigDir());
    }

    public function test_it_returns_phpunit_absolute_dir_from_config_with_absolute_path()
    {
        $absolutePath = '/app';
        $json = sprintf('{"phpUnit": {"configDir": "%s"}}', $absolutePath);
        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $expected = $absolutePath;

        $this->assertSame(p($expected), p($config->getPhpUnitConfigDir()));
    }

    public function test_it_returns_phpunit_config_dir_from_config()
    {
        $phpUnitConfigDir = 'app';
        $json = sprintf('{"phpUnit": {"configDir": "%s"}}', $phpUnitConfigDir);
        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $expected = '/path/to/config/app';

        $this->assertSame(p($expected), p($config->getPhpUnitConfigDir()));
    }

    public function test_it_returns_default_source_dirs_with_no_config()
    {
        $config = new InfectionConfig(new \stdClass(), $this->filesystem, '/path/to/config');

        $this->assertSame(InfectionConfig::DEFAULT_SOURCE_DIRS, $config->getSourceDirs());
    }

    public function test_it_returns_source_dirs_from_config()
    {
        $excludedFolders = '["source-folder"]';
        $json = sprintf('{"source": {"directories": %s}}', $excludedFolders);
        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $this->assertSame(['source-folder'], $config->getSourceDirs());
    }

    public function test_it_returns_default_exclude_dirs_with_no_config()
    {
        $config = new InfectionConfig(new \stdClass(), $this->filesystem, '/path/to/config');

        $this->assertSame(InfectionConfig::DEFAULT_EXCLUDE_DIRS, $config->getSourceExcludePaths());
    }

    public function test_it_returns_exclude_dirs_from_config_with_excludes_option()
    {
        $json = '{"source": {"excludes":["subfolder/excluded-folder"], "directories": ["source"]}}';
        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $this->assertSame(['subfolder/excluded-folder'], $config->getSourceExcludePaths());
    }

    public function test_it_excludes_by_glob_patterns()
    {
        $srcDir = __DIR__ . '/../Fixtures/Files/phpunit/project-path';
        $json = sprintf('{"source": {"excludes":["exclude/exclude*"], "directories": ["%s"]}}', p($srcDir));

        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $excludedDirs = $config->getSourceExcludePaths();

        $this->assertCount(2, $excludedDirs);
    }

    public function test_it_returns_default_temp_dir()
    {
        $config = new InfectionConfig(json_decode('{}'), $this->filesystem, '/path/to/config');

        $this->assertSame(sys_get_temp_dir(), $config->getTmpDir());
    }

    public function test_it_returns_default_temp_dir_with_empty_setting()
    {
        $config = new InfectionConfig(json_decode('{"tmpDir": ""}'), $this->filesystem, '/path/to/config');

        $this->assertSame(sys_get_temp_dir(), $config->getTmpDir());
    }

    public function test_it_returns_temp_dir_from_config_with_absolute_path()
    {
        $config = new InfectionConfig(json_decode('{"tmpDir": "/root/test"}'), $this->filesystem, '/path/to/config');

        $this->assertSame('/root/test', $config->getTmpDir());
    }

    public function test_it_returns_temp_dir_from_config_with_relative_path()
    {
        $config = new InfectionConfig(json_decode('{"tmpDir": "relative/folder"}'), $this->filesystem, '/path/to/config');

        $this->assertSame('/path/to/config/relative/folder', $config->getTmpDir());
    }

    public function test_it_returns_correct_phpunit_custom_path()
    {
        $config = new InfectionConfig(json_decode('{"phpUnit": {"customPath":"app"}}'), $this->filesystem, '/path/to/config');

        $this->assertSame('app', $config->getPhpUnitCustomPath());
    }

    public function test_it_correctly_gets_config_logs()
    {
        $config = new InfectionConfig(json_decode('{"logs": {"text":"app", "debug":"location"}}'), $this->filesystem, '/path/to/config');

        $this->assertSame(['text' => 'app', 'debug' => 'location'], $config->getLogsTypes());
    }

    public function test_it_correctly_gets_config_logs_if_missing()
    {
        $config = new InfectionConfig(new \stdClass(), $this->filesystem, '/path/to/config');

        $this->assertSame([], $config->getLogsTypes());
    }

    public function test_it_sets_ignored_mutators()
    {
        $config = <<<'JSON'
{
    "mutators": {
        "PublicVisibility": {
            "ignore": [
                "Ignore\\For\\Particular\\Class",
                "Ignore\\For\\Another\\Class::method",
                "Ignore\\For\\**\\*\\Glob\\Pattern\\Or\\Namespace"
            ]
        }
    }
}

JSON;

        $config = new InfectionConfig(json_decode($config), $this->filesystem, '/path/to/config');
        $this->assertSame(
            ['ignore' => [
                    "Ignore\For\Particular\Class",
                    "Ignore\For\Another\Class::method",
                    "Ignore\For\**\*\Glob\Pattern\Or\Namespace",
                ],
            ],
            (array) $config->getMutatorsConfiguration()['PublicVisibility']);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param \stdClass $config Settings
     * @param string $methodName Method To Call
     * @param string $result Correct Response
     */
    public function test_config(\stdClass $config, string $methodName, string $result)
    {
        $testSubject = new InfectionConfig(
            $config,
            $this->filesystem,
            '/path/to/config'
        );

        $this->assertSame($result, $testSubject->{$methodName}());
    }

    public function configDataProvider(): \Generator
    {
        yield 'It uses the default framework (PHPUnit)' => [
            (object) [],
            'getTestFramework',
            'phpunit',
        ];

        yield 'It uses the registered framework (phpspec)' => [
            (object) [
                'testFramework' => 'phpspec',
            ],
            'getTestFramework',
            'phpspec',
        ];

        yield 'It returns an empty bootstrap' => [
            (object) [],
            'getBootstrap',
            '',
        ];

        yield 'It returns the bootstrap file' => [
            (object) [
                'bootstrap' => 'bootstrap.php',
            ],
            'getBootstrap',
            'bootstrap.php',
        ];
    }
}
