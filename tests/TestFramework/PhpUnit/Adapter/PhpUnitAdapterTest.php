<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Adapter;


use Infection\Finder\AbstractExecutableFinder;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\Utils\VersionParser;
use Mockery;

class PhpUnitAdapterTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @dataProvider passProvider
     */
    public function test_it_determines_whether_tests_pass_or_not($output, $expectedResult)
    {
        $executableFined = Mockery::mock(AbstractExecutableFinder::class);
        $initialConfigBuilder = Mockery::mock(InitialConfigBuilder::class);
        $mutationConfigBuilder = Mockery::mock(MutationConfigBuilder::class);
        $cliArgumentsBuilder = Mockery::mock(CommandLineArgumentsAndOptionsBuilder::class);
        $versionParser = Mockery::mock(VersionParser::class);

        $adapter = new PhpUnitAdapter($executableFined, $initialConfigBuilder, $mutationConfigBuilder, $cliArgumentsBuilder, $versionParser);

        $result = $adapter->testsPass($output);

        $this->assertSame($expectedResult, $result);
    }

    public function passProvider()
    {
        return [
            ['OK, but incomplete, skipped, or risky tests!', true],
            ['OK (5 tests, 3 assertions)', true],
            ['FAILURES!', false],
            ['ERRORS!', false],
        ];
    }
}