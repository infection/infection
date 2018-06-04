<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpSpec\Config\Builder;

use Infection\TestFramework\PhpSpec\Config\Builder\InitialConfigBuilder;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class InitialConfigBuilderTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var string
     */
    private $workspace;

    protected function setUp()
    {
        $this->workspace = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);
        $this->fileSystem = new Filesystem();

        $this->tmpDir = (new TmpDirectoryCreator($this->fileSystem))->createAndGet($this->workspace);
    }

    protected function tearDown()
    {
        $this->fileSystem->remove($this->workspace);
    }

    public function test_it_builds_path_to_initial_config_file()
    {
        $originalYamlConfigPath = __DIR__ . '/../../../../Fixtures/Files/phpspec/phpspec.yml';

        $builder = new InitialConfigBuilder($this->tmpDir, $originalYamlConfigPath, false);

        $this->assertSame($this->tmpDir . '/phpspecConfiguration.initial.infection.yml', $builder->build());
    }
}
