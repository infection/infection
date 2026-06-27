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

namespace Infection\Tests\Console;

use Infection\Console\Application;
use Infection\Framework\InfectionVersion;
use Infection\Testing\SingletonContainer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(Application::class)]
final class ApplicationTest extends TestCase
{
    public function test_it_uses_the_infection_version(): void
    {
        $versionMock = $this->createMock(InfectionVersion::class);
        $versionMock
            ->expects($this->once())
            ->method('prettyVersion')
            ->willReturn('1.2.3');

        $application = new Application(
            SingletonContainer::getContainer(),
            $versionMock,
        );

        $this->assertSame('1.2.3', $application->getVersion());
    }

    #[DataProvider('provideNonCommandArguments')]
    public function test_it_routes_to_run_command_when_first_argument_is_not_a_known_command(string $argument): void
    {
        $application = new Application(SingletonContainer::getContainer());
        $application->setAutoExit(false);
        $application->setCatchExceptions(true);

        $output = new BufferedOutput();
        $application->run(new StringInput($argument), $output);

        $display = $output->fetch();

        $this->assertStringNotContainsString(sprintf('Command "%s" is not defined', $argument), $display);
        $this->assertStringNotContainsString(sprintf("Command '%s' is not defined", $argument), $display);
    }

    public static function provideNonCommandArguments(): iterable
    {
        yield 'source directory' => ['src/'];

        yield 'test directory' => ['tests/'];

        yield 'nested path' => ['src/Foo/Bar/'];
    }

    public function test_it_does_not_alter_routing_for_known_commands(): void
    {
        $application = new Application(SingletonContainer::getContainer());
        $application->setAutoExit(false);
        $application->setCatchExceptions(true);

        $output = new BufferedOutput();
        $application->run(new StringInput('run --help'), $output);

        $display = $output->fetch();

        $this->assertStringContainsString('Runs the mutation testing', $display);
    }

    public function test_it_configures_the_input_when_routing_to_the_run_command(): void
    {
        $command = $this->createRunCommandSpy();

        $application = new Application(SingletonContainer::getContainer());
        $application->setAutoExit(false);
        $application->addCommands([$command]);

        $application->run(new StringInput('src/ --no-interaction'), new BufferedOutput());

        $this->assertFalse($command->wasInteractive);
    }

    public function test_it_preserves_programmatic_interactivity_when_routing_to_the_run_command(): void
    {
        $command = $this->createRunCommandSpy();

        $application = new Application(SingletonContainer::getContainer());
        $application->setAutoExit(false);
        $application->addCommands([$command]);

        $input = new StringInput('src/');
        $input->setInteractive(false);

        $application->run($input, new BufferedOutput());

        $this->assertFalse($command->wasInteractive);
    }

    /**
     * Replaces the real `run` command with a spy recording the interactivity of
     * the input it ends up receiving.
     *
     * @return Command&object{wasInteractive: bool|null}
     */
    private function createRunCommandSpy(): Command
    {
        return new class extends Command {
            public ?bool $wasInteractive = null;

            protected function configure(): void
            {
                $this
                    ->setName('run')
                    ->addArgument('paths', InputArgument::IS_ARRAY | InputArgument::OPTIONAL);
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $this->wasInteractive = $input->isInteractive();

                return Command::SUCCESS;
            }
        };
    }
}
