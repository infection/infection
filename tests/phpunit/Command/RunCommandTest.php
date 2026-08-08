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

use Infection\Command\RunCommand;
use Infection\Console\Application;
use Infection\Container\Container;
use Infection\FileSystem\Locator\RootsFileOrDirectoryLocator;
use Infection\Metrics\MaxTimeoutCountReached;
use Infection\Metrics\MinMsiCheckFailed;
use Infection\Process\Runner\InitialTestsFailed;
use Infection\Source\Exception\NoSourceFound;
use Infection\Testing\SingletonContainer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Throwable;

#[CoversClass(RunCommand::class)]
final class RunCommandTest extends TestCase
{
    public function test_it_fails_when_threads_value_is_string_but_not_max(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value of option `--threads` must be of type integer or string "max". String "abc" provided.');

        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('run'));

        $result = $tester->execute(['--threads' => 'abc']);
        $this->assertSame(1, $result);
    }

    public function test_it_fails_when_show_mutations_value_is_string_but_not_max(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value of option `--show-mutations` must be of type integer or string "max". String "abc" provided.');

        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('run'));

        $result = $tester->execute(['--show-mutations' => 'abc']);
        $this->assertSame(1, $result);
    }

    public function test_it_fails_when_both_test_framework_option_names_are_passed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot pass both the legacy option "--test-framework-options" and "--test-framework-extra-args".');

        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('run'));

        $tester->execute([
            '--test-framework-options' => '',
            '--test-framework-extra-args' => '',
        ]);
    }

    public function test_it_fails_when_positional_source_path_and_filter_option_are_both_provided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot pass source paths as positional arguments together with the "--filter" option. Use either form, not both.');

        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('run'));

        $tester->execute([
            'paths' => ['src/Engine.php'],
            '--filter' => 'src/Engine.php',
        ]);
    }

    public function test_it_fails_when_positional_test_path_and_test_framework_extra_args_are_both_provided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot pass test paths as positional arguments together with the "--test-framework-extra-args" option.');

        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('run'));

        $tester->execute([
            'paths' => ['tests/phpunit/EngineTest.php'],
            '--test-framework-extra-args' => 'tests/phpunit/EngineTest.php',
        ]);
    }

    public function test_it_fails_when_positional_source_path_and_git_diff_filter_are_both_provided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot pass positional paths together with "--git-diff-filter" / "--git-diff-lines". Use either form, not both.');

        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('run'));

        $tester->execute([
            'paths' => ['src/Engine.php'],
            '--git-diff-filter' => 'AM',
        ]);
    }

    public function test_it_fails_when_a_positional_path_does_not_exist_on_disk(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid path argument "src/DefinitelyDoesNotExist.php": multiple paths must be passed as separate arguments.');

        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('run'));

        $tester->execute([
            'paths' => ['src/DefinitelyDoesNotExist.php'],
        ]);
    }

    public function test_it_fails_when_a_positional_argument_is_an_fqcn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('FQCN-style arguments like "\App\Foo" are not yet supported.');

        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('run'));

        $tester->execute([
            'paths' => ['\App\Foo'],
        ]);
    }

    public function test_it_succeeds_when_no_source_to_mutate_was_found_because_of_a_filter(): void
    {
        $failure = NoSourceFound::noExecutableSourceCodeForDiff();

        $tester = $this->createCommandTesterFailingOnStartUp($failure);

        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('[OK] ' . $failure->getMessage(), $tester->getDisplay(normalize: true));
    }

    public function test_it_rethrows_when_no_source_to_mutate_was_found_without_a_filter(): void
    {
        $expected = NoSourceFound::noExecutableSourceCode();

        $tester = $this->createCommandTesterFailingOnStartUp($expected);

        $this->expectExceptionObject($expected);

        $tester->execute([]);
    }

    #[DataProvider('reportedFailureProvider')]
    public function test_it_reports_a_run_failure_as_an_error(Throwable $failure): void
    {
        $tester = $this->createCommandTesterFailingOnStartUp($failure);

        $tester->execute([]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('[ERROR] ' . $failure->getMessage(), $tester->getDisplay(normalize: true));
    }

    /**
     * @return iterable<string, array{Throwable}>
     */
    public static function reportedFailureProvider(): iterable
    {
        yield 'initial tests failed' => [new InitialTestsFailed('Project tests must be in a passing state before running Infection.')];

        yield 'minimum MSI not reached' => [MinMsiCheckFailed::createForMsi(80.0, 20.0)];

        yield 'too many timeouts' => [MaxTimeoutCountReached::create(1, 2)];
    }

    private function createCommandTesterFailingOnStartUp(Throwable $failure): CommandTester
    {
        // The command catches around the start-up steps as well as the engine run. The locator is
        // the first service the start-up asks for, so failing it keeps the test off the disk.
        $container = Container::create()->cloneWithService(
            RootsFileOrDirectoryLocator::class,
            static fn (): never => throw $failure,
        );

        $application = new Application($container);

        return new CommandTester($application->find('run'));
    }
}
