<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Adapter;

use Infection\Finder\AbstractExecutableFinder;
use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\MemoryUsageAware;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\Utils\VersionParser;
use Mockery;

/**
 * @internal
 */
final class PhpUnitAdapterTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    public function test_it_has_a_name()
    {
        $adapter = $this->getAdapter();

        $this->assertSame('PHPUnit', $adapter->getName());
    }

    /**
     * @dataProvider passProvider
     */
    public function test_it_determines_whether_tests_pass_or_not($output, $expectedResult)
    {
        $adapter = $this->getAdapter();

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

    public function test_it_conforms_to_memory_usage_aware()
    {
        $adapter = $this->getAdapter();
        $this->assertInstanceOf(MemoryUsageAware::class, $adapter);
    }

    /**
     * @dataProvider memoryReportProvider
     */
    public function test_it_determines_used_memory_amount($output, $expectedResult)
    {
        $adapter = $this->getAdapter();

        $result = $adapter->getMemoryUsed($output);

        $this->assertSame($expectedResult, $result);
    }

    public function memoryReportProvider()
    {
        return [
            ['Memory: 8.00MB', 8.0],
            ['Memory: 68.00MB', 68.0],
            ['Time: 2.51 seconds', -1.0],
        ];
    }

    private function getAdapter(): PhpUnitAdapter
    {
        $executableFined = Mockery::mock(AbstractExecutableFinder::class);
        $initialConfigBuilder = Mockery::mock(InitialConfigBuilder::class);
        $mutationConfigBuilder = Mockery::mock(MutationConfigBuilder::class);
        $cliArgumentsBuilder = Mockery::mock(CommandLineArgumentsAndOptionsBuilder::class);
        $versionParser = Mockery::mock(VersionParser::class);

        return new PhpUnitAdapter(
            $executableFined,
            $initialConfigBuilder,
            $mutationConfigBuilder,
            $cliArgumentsBuilder,
            $versionParser
        );
    }
}
