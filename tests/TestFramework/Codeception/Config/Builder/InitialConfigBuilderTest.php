<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Codeception\Config\Builder;

use Infection\TestFramework\Codeception\Config\Builder\InitialConfigBuilder;
use PHPUnit\Framework\TestCase;

class InitialConfigBuilderTest extends TestCase
{
    public function test_it_builds_path_to_initial_config_file()
    {
        $originalConfigPath = 'original/config/path';

        $builder = new InitialConfigBuilder($originalConfigPath);

        $this->assertSame($originalConfigPath, $builder->build());
    }
}
