<?php

declare(strict_types=1);

namespace TestFramework\PhpSpec\Config\Builder;

use Infection\TestFramework\PhpSpec\Config\Builder\InitialConfigBuilder;
use Infection\Utils\TempDirectoryCreator;
use PHPUnit\Framework\TestCase;

class InitialConfigBuilderTest extends TestCase
{
    public function test_it_builds_path_to_initial_config_file()
    {
        $tempDirCreator = new TempDirectoryCreator();
        $tempDir = $tempDirCreator->createAndGet('infection-test');
        $originalYamlConfigPath = __DIR__ . '/../../../../Files/phpspec/phpspec.yml';

        $builder = new InitialConfigBuilder($tempDir, $originalYamlConfigPath);

        $this->assertSame($tempDir . '/phpspecConfiguration.initial.infection.yml', $builder->build());
    }
}