<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Coverage;

use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\Coverage\TestFileNameNotFoundException;
use Infection\TestFramework\PhpUnit\Coverage\PhpUnitTestFileDataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PhpUnitTestFileDataProviderTest extends TestCase
{
    /**
     * @var PhpUnitTestFileDataProvider
     */
    private $infoProvider;

    protected function setUp(): void
    {
        $this->infoProvider = new PhpUnitTestFileDataProvider(
            __DIR__ . '/../../../Fixtures/Files/phpunit/junit.xml'
        );
    }

    public function test_it_throws_an_exception_if_class_is_not_found(): void
    {
        $this->expectException(TestFileNameNotFoundException::class);

        $this->infoProvider->getTestFileInfo('abc');
    }

    public function test_it_returns_time_and_path(): void
    {
        $info = $this->infoProvider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');

        $this->assertSame('/project/tests/Config/InfectionConfigTest.php', $info['path']);
        $this->assertSame(0.021983, $info['time']);
    }

    public function test_consecutive_calls_with_the_same_class_return_the_same_result(): void
    {
        $info1 = $this->infoProvider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');
        $info2 = $this->infoProvider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');

        $this->assertSame($info1, $info2);
    }

    public function test_it_throws_a_coverage_does_not_exists_exception_when_junit_file_does_not_exist(): void
    {
        $provider = new PhpUnitTestFileDataProvider('foo/bar/fake-file');

        $this->expectException(CoverageDoesNotExistException::class);

        $provider->getTestFileInfo('Foo\BarTest');
    }
}
