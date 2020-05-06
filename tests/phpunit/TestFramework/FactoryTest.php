<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework;

use function array_keys;
use Infection\AbstractTestFramework\UnsupportedTestFrameworkVersion;
use Infection\Configuration\Configuration;
use Infection\FileSystem\Finder\TestFrameworkFinder;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use Infection\TestFramework\Factory;
use Infection\Tests\Fixtures\Logger\FakeLogger;
use Infection\Tests\Fixtures\TestFramework\DummyTestFrameworkAdapter;
use Infection\Tests\Fixtures\TestFramework\DummyTestFrameworkFactory;
use Infection\Tests\Fixtures\TestFramework\FailedVersionCheckTestFrameworkAdapter;
use Infection\Tests\Fixtures\TestFramework\FailedVersionCheckTestFrameworkFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

/**
 * @group integration
 */
final class FactoryTest extends TestCase
{
    public function test_it_throws_an_exception_if_it_cant_find_the_test_framework(): void
    {
        $factory = new Factory(
            '',
            '',
            $this->createMock(TestFrameworkConfigLocatorInterface::class),
            $this->createMock(TestFrameworkFinder::class),
            '',
            $this->createMock(Configuration::class),
            [],
            new FakeLogger()
        );

        $this->expectException(InvalidArgumentException::class);

        $factory->create('Fake Test Framework', false);
    }

    public function test_it_uses_installed_test_framework_adapters(): void
    {
        $factory = new Factory(
            '',
            '',
            $this->createMock(TestFrameworkConfigLocatorInterface::class),
            $this->createMock(TestFrameworkFinder::class),
            '',
            $this->createMock(Configuration::class),
            [
                'infection/codeception-adapter' => [
                    'install_path' => '/path/to/dummy/adapter/factory.php',
                    'extra' => ['class' => DummyTestFrameworkFactory::class],
                    'version' => '1.0.0',
                ],
            ],
            new FakeLogger()
        );

        $adapter = $factory->create('dummy', false);

        $this->assertInstanceOf(DummyTestFrameworkAdapter::class, $adapter);
    }

    public function test_it_logs_an_error_if_the_test_framework_adapter_detects_an_unsupported_version(): void
    {
        $logger = new TestLogger();

        $factory = new Factory(
            '',
            '',
            $this->createMock(TestFrameworkConfigLocatorInterface::class),
            $this->createMock(TestFrameworkFinder::class),
            '',
            $this->createMock(Configuration::class),
            [
                'infection/codeception-adapter' => [
                    'install_path' => '/path/to/dummy/adapter/factory.php',
                    'extra' => ['class' => FailedVersionCheckTestFrameworkFactory::class],
                    'version' => '1.0.0',
                ],
            ],
            $logger
        );

        $adapter = $factory->create('dummy', false);

        $this->assertInstanceOf(FailedVersionCheckTestFrameworkAdapter::class, $adapter);

        $this->assertCount(1, $logger->records);

        $record = $logger->records[0];

        $this->assertSame(['level', 'message', 'context'], array_keys($record));

        $recordLevel = $record['level'];
        $recordMessage = $record['message'];
        $recordContext = $record['context'];

        $this->assertSame(LogLevel::ERROR, $recordLevel);
        $this->assertSame(
            'The FailedVersionCheckAdapter version "X" is not supported. The version detected is either a non-stable version or older than "Y". Infection may not run as intended',
            $recordMessage
        );

        $this->assertSame(['exception'], array_keys($recordContext));
        $this->assertInstanceOf(UnsupportedTestFrameworkVersion::class, $recordContext['exception']);
    }
}
