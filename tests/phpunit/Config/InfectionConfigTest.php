<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Config;

use Infection\Config\InfectionConfig;
use Infection\Tests\Fixtures\StubMutator;
use function Infection\Tests\normalizePath as p;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class InfectionConfigTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
    }

    public function test_it_returns_default_timeout_with_no_config(): void
    {
        $config = new InfectionConfig(new \stdClass(), $this->filesystem, '/path/to/config');

        $this->assertSame(InfectionConfig::PROCESS_TIMEOUT_SECONDS, $config->getProcessTimeout());
    }

    public function test_it_returns_timeout_from_config(): void
    {
        $timeout = 3;
        $json = sprintf('{"timeout": %d}', $timeout);
        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $this->assertSame($timeout, $config->getProcessTimeout());
    }

    public function test_it_returns_default_phpunit_config_dir_with_no_config(): void
    {
        $config = new InfectionConfig(new \stdClass(), $this->filesystem, '/path/to/config');

        $this->assertSame('/path/to/config', $config->getPhpUnitConfigDir());
    }

    public function test_it_returns_phpunit_absolute_dir_from_config_with_absolute_path(): void
    {
        $absolutePath = '/app';
        $json = sprintf('{"phpUnit": {"configDir": "%s"}}', $absolutePath);
        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $this->assertSame(p($absolutePath), p($config->getPhpUnitConfigDir()));
    }

    public function test_it_returns_phpunit_config_dir_from_config(): void
    {
        $phpUnitConfigDir = 'app';
        $json = sprintf('{"phpUnit": {"configDir": "%s"}}', $phpUnitConfigDir);
        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $expected = '/path/to/config/app';

        $this->assertSame(p($expected), p($config->getPhpUnitConfigDir()));
    }

    public function test_it_returns_default_source_dirs_with_no_config(): void
    {
        $config = new InfectionConfig(new \stdClass(), $this->filesystem, '/path/to/config');

        $this->assertSame(InfectionConfig::DEFAULT_SOURCE_DIRS, $config->getSourceDirs());
    }

    public function test_it_returns_source_dirs_from_config(): void
    {
        $excludedFolders = '["source-folder"]';
        $json = sprintf('{"source": {"directories": %s}}', $excludedFolders);
        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $this->assertSame(['source-folder'], $config->getSourceDirs());
    }

    public function test_it_returns_default_exclude_dirs_with_no_config(): void
    {
        $config = new InfectionConfig(new \stdClass(), $this->filesystem, '/path/to/config');

        $this->assertSame(InfectionConfig::DEFAULT_EXCLUDE_DIRS, $config->getSourceExcludePaths());
    }

    public function test_it_returns_exclude_dirs_from_config_with_excludes_option(): void
    {
        $json = '{"source": {"excludes":["subfolder/excluded-folder"], "directories": ["source"]}}';
        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $this->assertSame(['subfolder/excluded-folder'], $config->getSourceExcludePaths());
    }

    public function test_it_excludes_by_glob_patterns(): void
    {
        $srcDir = __DIR__ . '/../Fixtures/Files/phpunit/project-path';
        $json = sprintf('{"source": {"excludes":["exclude/exclude*"], "directories": ["%s"]}}', p($srcDir));

        $config = new InfectionConfig(json_decode($json), $this->filesystem, '/path/to/config');

        $excludedDirs = $config->getSourceExcludePaths();

        $this->assertCount(2, $excludedDirs);
    }

    public function test_it_returns_default_temp_dir(): void
    {
        $config = new InfectionConfig(json_decode('{}'), $this->filesystem, '/path/to/config');

        $this->assertSame(sys_get_temp_dir(), $config->getTmpDir());
    }

    public function test_it_returns_default_temp_dir_with_empty_setting(): void
    {
        $config = new InfectionConfig(json_decode('{"tmpDir": ""}'), $this->filesystem, '/path/to/config');

        $this->assertSame(sys_get_temp_dir(), $config->getTmpDir());
    }

    public function test_it_returns_temp_dir_from_config_with_absolute_path(): void
    {
        $config = new InfectionConfig(json_decode('{"tmpDir": "/root/test"}'), $this->filesystem, '/path/to/config');

        $this->assertSame('/root/test', $config->getTmpDir());
    }

    public function test_it_returns_temp_dir_from_config_with_relative_path(): void
    {
        $config = new InfectionConfig(json_decode('{"tmpDir": "relative/folder"}'), $this->filesystem, '/path/to/config');

        $this->assertSame('/path/to/config/relative/folder', $config->getTmpDir());
    }

    public function test_it_returns_correct_phpunit_custom_path(): void
    {
        $config = new InfectionConfig(json_decode('{"phpUnit": {"customPath":"app"}}'), $this->filesystem, '/path/to/config');

        $this->assertSame('app', $config->getPhpUnitCustomPath());
    }

    public function test_it_correctly_gets_config_logs(): void
    {
        $config = new InfectionConfig(json_decode('{"logs": {"text":"app", "debug":"location"}}'), $this->filesystem, '/path/to/config');

        $this->assertSame(['text' => 'app', 'debug' => 'location'], $config->getLogsTypes());
    }

    public function test_it_correctly_gets_config_logs_if_missing(): void
    {
        $config = new InfectionConfig(new \stdClass(), $this->filesystem, '/path/to/config');

        $this->assertSame([], $config->getLogsTypes());
    }

    public function test_it_sets_ignored_mutators(): void
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

    public function test_it_accepts_custom_mutators(): void
    {
        $config = <<<'JSON'
{
    "mutators": {
        "Infection\\Tests\\Fixtures\\StubMutator": true
    }
}
JSON;

        $config = new InfectionConfig(json_decode($config), $this->filesystem, '/path/to/config');
        $this->assertSame(
            [StubMutator::class => true],
            $config->getMutatorsConfiguration());
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param \stdClass $config Settings
     * @param string $methodName Method To Call
     * @param string $result Correct Response
     */
    public function test_config(\stdClass $config, string $methodName, string $result): void
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

        yield 'It returns empty initial test php options' => [
            (object) [],
            'getInitialTestsPhpOptions',
            '',
        ];

        yield 'It returns initial test php options' => [
            (object) [
                'initialTestsPhpOptions' => '-d xdebug.remote_autostart=1',
            ],
            'getInitialTestsPhpOptions',
            '-d xdebug.remote_autostart=1',
        ];

        yield 'It returns empty framework options' => [
            (object) [],
            'getTestFrameworkOptions',
            '',
        ];

        yield 'It returns framework options' => [
            (object) [
                'testFrameworkOptions' => '-vvv',
            ],
            'getTestFrameworkOptions',
            '-vvv',
        ];
    }
}
