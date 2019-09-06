<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Codeception\Config\Builder;

use Infection\TestFramework\Codeception\Config\Builder\InitialConfigBuilder;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

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

    protected function setUp(): void
    {
        $this->workspace = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);
        $this->fileSystem = new Filesystem();

        $this->tmpDir = (new TmpDirectoryCreator($this->fileSystem))->createAndGet($this->workspace);
    }

    protected function tearDown(): void
    {
        $this->fileSystem->remove($this->workspace);
    }

    public function test_it_builds_path_to_initial_config_file(): void
    {
        $originalYamlConfigPath = __DIR__ . '/../../../../Fixtures/Files/codeception/codeception.yml';

        $builder = new InitialConfigBuilder(
            $this->tmpDir,
            dirname($originalYamlConfigPath),
            Yaml::parseFile($originalYamlConfigPath),
            false,
            ['src']
        );

        $this->assertSame($this->tmpDir . '/codeception.initial.infection.yml', $builder->build('2.0'));
        $this->assertFileExists($this->tmpDir . '/codeception.initial.infection.yml');
    }
}
