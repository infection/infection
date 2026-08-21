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

namespace Infection\Tests\Command;

use Infection\Command\TestFrameworkCommand;
use Infection\Console\Application;
use Infection\Container\Container;
use Infection\TestFramework\CompositeTestFramework;
use Infection\TestFramework\Contracts\TestFramework;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(TestFrameworkCommand::class)]
final class TestFrameworkCommandTest extends TestCase
{
    public function test_it_displays_the_registered_test_framework(): void
    {
        $testFramework = $this->createStub(TestFramework::class);
        $testFramework->method('getName')->willReturn('PHPUnit');
        $testFramework->method('getVersion')->willReturn('12.0.0');
        $testFramework->method('getBinary')->willReturn('/project/vendor/bin/phpunit');
        $staticAnalysisFramework = $this->createStub(TestFramework::class);
        $staticAnalysisFramework->method('getName')->willReturn('PHPStan');
        $staticAnalysisFramework->method('getVersion')->willReturn('2.1.0');
        $staticAnalysisFramework->method('getBinary')->willReturn('/project/vendor/bin/phpstan');
        $container = Container::create();
        $container->set(
            TestFramework::class,
            static fn () => new CompositeTestFramework([$testFramework, $staticAnalysisFramework]),
        );
        $application = new Application($container);
        $tester = new CommandTester($application->find('test-framework'));

        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Name', $tester->getDisplay());
        $this->assertStringContainsString('PHPUnit', $tester->getDisplay());
        $this->assertStringContainsString('Version', $tester->getDisplay());
        $this->assertStringContainsString('12.0.0', $tester->getDisplay());
        $this->assertStringContainsString('Binary', $tester->getDisplay());
        $this->assertStringContainsString('/project/vendor/bin/phpunit', $tester->getDisplay());
        $this->assertStringContainsString('PHPStan', $tester->getDisplay());
        $this->assertStringContainsString('2.1.0', $tester->getDisplay());
        $this->assertStringContainsString('/project/vendor/bin/phpstan', $tester->getDisplay());
    }
}
