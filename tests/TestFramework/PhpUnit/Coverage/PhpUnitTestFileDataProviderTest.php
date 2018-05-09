<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\Coverage;

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

    protected function setUp()
    {
        $this->infoProvider = new PhpUnitTestFileDataProvider(
            __DIR__ . '/../../../Fixtures/Files/phpunit/junit.xml'
        );
    }

    /**
     * @expectedException \Infection\TestFramework\Coverage\TestFileNameNotFoundException
     */
    public function test_it_throws_an_exception_if_class_is_not_found()
    {
        $this->infoProvider->getTestFileInfo('abc');
    }

    public function test_it_returns_time_and_path()
    {
        $info = $this->infoProvider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');

        $this->assertSame('/project/tests/Config/InfectionConfigTest.php', $info['path']);
        $this->assertSame(0.021983, $info['time']);
    }

    public function test_consecutive_calls_with_the_same_class_return_the_same_result()
    {
        $info1 = $this->infoProvider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');
        $info2 = $this->infoProvider->getTestFileInfo('Infection\Tests\Config\InfectionConfigTest');

        $this->assertSame($info1, $info2);
    }
}
