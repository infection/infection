<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Codeception\Adapter;

use Infection\Finder\AbstractExecutableFinder;
use Infection\TestFramework\Codeception\Adapter\CodeceptionAdapter;
use Infection\TestFramework\Codeception\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\Codeception\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\Utils\VersionParser;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CodeceptionAdapterTest extends MockeryTestCase
{
    /**
     * @dataProvider passProvider
     */
    public function test_it_determines_whether_tests_pass_or_not(string $output, bool $expectedResult)
    {
        $executableFined = Mockery::mock(AbstractExecutableFinder::class);
        $initialConfigBuilder = Mockery::mock(InitialConfigBuilder::class);
        $mutationConfigBuilder = Mockery::mock(MutationConfigBuilder::class);
        $cliArgumentsBuilder = Mockery::mock(CommandLineArgumentsAndOptionsBuilder::class);
        $versionParser = Mockery::mock(VersionParser::class);

        $adapter = new CodeceptionAdapter($executableFined, $initialConfigBuilder, $mutationConfigBuilder, $cliArgumentsBuilder, $versionParser);

        $result = $adapter->testsPass($output);

        $this->assertSame($expectedResult, $result);
    }

    public function passProvider()
    {
        yield ['OK, but incomplete, skipped, or risky tests!', true];
        yield ['OK (5 tests, 3 assertions)', true];
        yield ['FAILURES!', false];
        yield ['ERRORS!', false];
    }
}
