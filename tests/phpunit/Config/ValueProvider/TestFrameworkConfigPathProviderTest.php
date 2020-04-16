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

namespace Infection\Tests\Config\ValueProvider;

use Exception;
use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\TestFrameworkConfigPathProvider;
use Infection\Console\IO;
use Infection\TestFramework\Config\TestFrameworkConfigLocatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use function Safe\realpath;
use Symfony\Component\Console\Input\StringInput;

/**
 * @group integration
 */
final class TestFrameworkConfigPathProviderTest extends BaseProviderTest
{
    /**
     * @var TestFrameworkConfigPathProvider
     */
    private $provider;

    /**
     * @var MockObject|TestFrameworkConfigLocatorInterface
     */
    private $locatorMock;

    /**
     * @var MockObject|ConsoleHelper
     */
    private $consoleMock;

    protected function setUp(): void
    {
        $this->locatorMock = $this->createMock(TestFrameworkConfigLocatorInterface::class);
        $this->consoleMock = $this->createMock(ConsoleHelper::class);
        $this->provider = new TestFrameworkConfigPathProvider(
            $this->locatorMock,
            $this->consoleMock,
            $this->getQuestionHelper()
        );
    }

    public function test_it_calls_locator_in_the_current_dir(): void
    {
        $this->locatorMock
            ->expects($this->once())
            ->method('locate');

        $result = $this->provider->get(
            new IO(
                new StringInput(''),
                $this->createStreamOutput()
            ),
            [],
            'phpunit'
        );

        $this->assertNull($result);
    }

    public function test_it_asks_question_if_no_config_is_found_in_current_dir(): void
    {
        $this->consoleMock
            ->expects($this->once())
            ->method('getQuestion')
            ->willReturn('foobar');

        $this->locatorMock
            ->expects($this->exactly(3))
            ->method('locate')
            ->will(
                $this->onConsecutiveCalls(
                    $this->throwException(new Exception()),
                    $this->throwException(new Exception()),
                    ''
                )
            );

        $inputPhpUnitPath = realpath(__DIR__ . '/../../Fixtures/Files/phpunit');

        $path = $this->provider->get(
            new IO(
                $this->createStreamableInput($this->getInputStream("{$inputPhpUnitPath}\n")),
                $this->createStreamOutput()
            ),
            [],
            'phpunit'
        );

        $this->assertSame($inputPhpUnitPath, $path);
        $this->assertDirectoryExists($path);
    }

    public function test_it_automatically_guesses_path(): void
    {
        $this->locatorMock
            ->expects($this->exactly(2))
            ->method('locate')
            ->will(
                $this->onConsecutiveCalls(
                    $this->throwException(new Exception()),
                    ''
                )
            );

        $this->consoleMock
            ->expects($this->never())
            ->method('getQuestion');

        $path = $this->provider->get(
            IO::createNull(),
            [],
            'phpunit'
        );

        $this->assertSame('.', $path);
    }

    public function test_validates_incorrect_dir(): void
    {
        if (!$this->hasSttyAvailable()) {
            $this->markTestSkipped('Stty is not available');
        }

        $this->locatorMock
            ->expects($this->exactly(3))
            ->method('locate')
            ->will(
                $this->onConsecutiveCalls(
                    $this->throwException(new Exception()),
                    $this->throwException(new Exception()),
                    ''
                )
            );

        $path = $this->provider->get(
            new IO(
                $this->createStreamableInput($this->getInputStream("abc\n")),
                $this->createStreamOutput()
            ),
            [],
            'phpunit'
        );

        $this->assertSame('.', $path); // fallbacks to default value
    }
}
