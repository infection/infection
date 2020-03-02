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

use Infection\TestFramework\AdapterInstallationDecider;
use PHPUnit\Framework\TestCase;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\rewind;
use function Safe\stream_get_contents;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * @group integration
 */
final class AdapterInstallationDeciderTest extends TestCase
{
    protected static $stty;

    protected function setUp(): void
    {
        parent::setUp();

        if (!$this->hasSttyAvailable()) {
            $this->markTestSkipped('Stty is not available');
        }
    }

    public function test_it_should_not_install_phpunit(): void
    {
        $installationDecider = new AdapterInstallationDecider(new QuestionHelper());

        $result = $installationDecider->shouldBeInstalled(
            'phpunit',
            $this->createMock(InputInterface::class),
            $this->createMock(Output::class)
        );

        $this->assertFalse($result, 'PHPUnit adapter should not be installed');
    }

    public function test_it_should_not_install_when_user_answers_no(): void
    {
        $installationDecider = new AdapterInstallationDecider(new QuestionHelper());

        $result = $installationDecider->shouldBeInstalled(
            'phpspec',
            $this->createStreamableInputInterfaceMock($this->getInputStream("no\n")),
            $this->createOutputInterface()
        );

        $this->assertFalse($result, 'Adapter should not be installed since user answered "no"');
    }

    public function test_it_should_install_with_non_interactive_mode(): void
    {
        $installationDecider = new AdapterInstallationDecider(new QuestionHelper());

        $result = $installationDecider->shouldBeInstalled(
            'phpspec',
            $this->createStreamableInputInterfaceMock($this->getInputStream("no\n"), false),
            $this->createOutputInterface()
        );

        $this->assertTrue($result, 'Adapter should be installed in non-interactive mode');
    }

    public function test_it_should_install_when_user_answers_yes(): void
    {
        $installationDecider = new AdapterInstallationDecider(new QuestionHelper());

        $streamOutput = $this->createOutputInterface();

        $result = $installationDecider->shouldBeInstalled(
            'phpspec',
            $this->createStreamableInputInterfaceMock($this->getInputStream("yes\n")),
            $streamOutput
        );

        $stream = $streamOutput->getStream();
        rewind($stream);
        $output = trim(stream_get_contents($stream));

        $this->assertTrue($result, 'Adapter should be installed since user answered "yes"');
        $this->assertStringContainsString(
            'Would you like to install infection/phpspec-adapter package? [yes]:',
            $output
        );
    }

    protected function getInputStream(string $input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fwrite($stream, $input);
        rewind($stream);

        return $stream;
    }

    protected function createOutputInterface(): StreamOutput
    {
        return new StreamOutput(fopen('php://memory', 'r+', false));
    }

    protected function createStreamableInputInterfaceMock($stream, $interactive = true)
    {
        $mock = $this->createMock(StreamableInputInterface::class);
        $mock->method('isInteractive')->willReturn($interactive);
        $mock->method('getStream')->willReturn($stream);

        return $mock;
    }

    protected function hasSttyAvailable(): bool
    {
        if (self::$stty !== null) {
            return self::$stty;
        }

        exec('stty 2>&1', $output, $exitcode);

        return self::$stty = $exitcode === 0;
    }
}
