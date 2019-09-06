<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Codeception\Adapter;

use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\Codeception\Adapter\CodeceptionAdapter;
use Infection\TestFramework\Config\InitialConfigBuilder;
use Infection\TestFramework\Config\MutationConfigBuilder;
use Infection\TestFramework\MemoryUsageAware;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Utils\VersionParser;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class CodeceptionAdapterTest extends TestCase
{
    /**
     * @var CodeceptionAdapter|MockObject
     */
    private $adapter;

    protected function setUp(): void
    {
        $initialConfigBuilder = $this->createMock(InitialConfigBuilder::class);
        $mutationConfigBuilder = $this->createMock(MutationConfigBuilder::class);
        $cliArgumentsBuilder = $this->createMock(CommandLineArgumentsAndOptionsBuilder::class);
        $versionParser = $this->createMock(VersionParser::class);

        $this->adapter = new CodeceptionAdapter(
            '/path/to/phpunit',
            $initialConfigBuilder,
            $mutationConfigBuilder,
            $cliArgumentsBuilder,
            $versionParser
        );
    }

    public function test_it_has_a_name(): void
    {
        $this->assertSame(TestFrameworkTypes::CODECEPTION, $this->adapter->getName());
    }

    /**
     * @dataProvider passProvider
     */
    public function test_it_determines_whether_tests_pass_or_not($output, $expectedResult): void
    {
        $result = $this->adapter->testsPass($output);

        $this->assertSame($expectedResult, $result);
    }

    public function test_it_conforms_to_memory_usage_aware(): void
    {
        $this->assertInstanceOf(MemoryUsageAware::class, $this->adapter);
    }

    /**
     * @dataProvider memoryReportProvider
     */
    public function test_it_determines_used_memory_amount($output, $expectedResult): void
    {
        $result = $this->adapter->getMemoryUsed($output);

        $this->assertSame($expectedResult, $result);
    }

    public function memoryReportProvider()
    {
        return [
            ['Memory: 8.00MB', 8.0],
            ['Memory: 68.00MB', 68.0],
            ['Memory: 68.00 MB', 68.0],
            ['Time: 2.51 seconds', -1.0],
        ];
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
