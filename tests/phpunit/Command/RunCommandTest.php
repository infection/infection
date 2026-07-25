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
use Infection\Testing\SingletonContainer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

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
}
