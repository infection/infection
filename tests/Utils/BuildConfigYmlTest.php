<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class BuildConfigYmlTest extends TestCase
{
    /**
     * @dataProvider providesYamlFilesForTesting
     */
    public function test_valid_yaml_has_key($filePath, $key)
    {
        $this->assertFileExists($filePath);
        $config = Yaml::parse(file_get_contents($filePath));
        $this->assertArrayHasKey($key, $config);
    }

    public function providesYamlFilesForTesting(): \Generator
    {
        $rootPath = __DIR__ . '/../../';

        yield [
            $rootPath . '.travis.yml',
            'script',
        ];

        yield [
            $rootPath . 'appveyor.yml',
            'test_script',
        ];

        yield [
            $rootPath . 'codecov.yml',
            'coverage',
        ];
    }
}
