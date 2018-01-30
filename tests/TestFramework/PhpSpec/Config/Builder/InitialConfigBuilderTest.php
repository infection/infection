<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpSpec\Config\Builder;

use Infection\Filesystem\Filesystem;
use Infection\TestFramework\PhpSpec\Config\Builder\InitialConfigBuilder;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;

class InitialConfigBuilderTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $tmpDir;

    protected function setUp()
    {
        $this->fileSystem = new Filesystem();
        $tmpDirCreator = new TmpDirectoryCreator($this->fileSystem);
        $this->tmpDir = $tmpDirCreator->createAndGet(sys_get_temp_dir() . '/infection-test');
    }

    protected function tearDown()
    {
        $this->fileSystem->remove($this->tmpDir);
    }

    public function test_it_builds_path_to_initial_config_file()
    {
        $originalYamlConfigPath = __DIR__ . '/../../../../Fixtures/Files/phpspec/phpspec.yml';

        $builder = new InitialConfigBuilder($this->tmpDir, $originalYamlConfigPath);

        $this->assertSame($this->tmpDir . '/phpspecConfiguration.initial.infection.yml', $builder->build());
    }
}
