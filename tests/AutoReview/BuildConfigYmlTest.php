<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\AutoReview;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 *
 * @group auto-review
 *
 * @coversNothing
 */
final class BuildConfigYmlTest extends TestCase
{
    /**
     * @dataProvider providesYamlFilesForTesting
     */
    public function test_valid_yaml_has_key($filePath)
    {
        $this->assertFileExists($filePath);

        try {
            Yaml::parse(file_get_contents($filePath));
        } catch (ParseException $e) {
            $this->fail(
                sprintf(
                    'Yaml file "%s" contains invalid yaml, and is used by our CI, please fix it. Original error message: "%s"',
                    realpath($filePath),
                    $e->getMessage()
                )
            );
        }
    }

    public function providesYamlFilesForTesting(): \Generator
    {
        $rootPath = __DIR__ . '/../../';

        yield [$rootPath . '.travis.yml'];

        yield [$rootPath . 'appveyor.yml'];

        yield [$rootPath . 'codecov.yml'];
    }
}
