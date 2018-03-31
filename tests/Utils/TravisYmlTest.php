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

class TravisYmlTest extends TestCase
{
    public function test_travis_yml_exists()
    {
        $this->assertFileExists(__DIR__ . '/../../.travis.yml');
    }

    public function test_travis_yml_valid_yaml()
    {
        $config = Yaml::parse(file_get_contents(__DIR__ . '/../../.travis.yml'));
        $this->assertArrayHasKey('script', $config);
    }
}
