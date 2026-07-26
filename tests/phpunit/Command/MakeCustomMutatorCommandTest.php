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

use Infection\Command\MakeCustomMutatorCommand;
use Infection\Console\Application;
use Infection\Container\Container;
use Infection\FileSystem\FileSystem;
use Infection\Tests\MockedContainer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[Group('integration')]
#[CoversClass(MakeCustomMutatorCommand::class)]
final class MakeCustomMutatorCommandTest extends TestCase
{
    public function test_it_create_custom_mutator_when_name_is_provided(): void
    {
        $workingDirectory = \Safe\getcwd();
        $app = new Application($this->createContainer());

        $tester = new CommandTester($app->find('make:mutator'));

        $result = $tester->execute([
            'Mutator name' => 'CustomMutator',
        ]);
        $this->assertSame(0, $result);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('Generated files', $display);
        $this->assertStringContainsString($workingDirectory . '/src/Mutator/CustomMutator.php', $display);
        $this->assertStringContainsString($workingDirectory . '/src/Mutator/CustomMutatorTest.php', $display);
    }

    public function test_it_create_custom_mutator_when_name_is_not_provided_at_command_call(): void
    {
        $app = new Application($this->createContainer());

        $tester = new CommandTester($app->find('make:mutator'));

        $tester->setInputs(['NewMutator']);

        $result = $tester->execute([]);

        $this->assertSame(0, $result);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('Generated files', $display);
        $this->assertStringContainsString('NewMutator.php', $display);
        $this->assertStringContainsString('NewMutator.php', $display);
    }

    public function test_it_trims_the_argument_value(): void
    {
        $app = new Application($this->createContainer());

        $tester = new CommandTester($app->find('make:mutator'));

        $result = $tester->execute([
            'Mutator name' => ' CustomMutator ',
        ]);
        $this->assertSame(0, $result);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('Generated files', $display);
        $this->assertStringContainsString('CustomMutator.php', $display);
        $this->assertStringContainsString('CustomMutatorTest.php', $display);
    }

    public function test_it_trims_the_value_from_quetion_helper(): void
    {
        $app = new Application($this->createContainer());

        $tester = new CommandTester($app->find('make:mutator'));

        $tester->setInputs([' NewMutator ']);

        $result = $tester->execute([]);

        $this->assertSame(0, $result);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('Generated files', $display);
        $this->assertStringContainsString('NewMutator.php', $display);
        $this->assertStringContainsString('NewMutator.php', $display);
    }

    public function test_it_uppercases_first_letter_of_argument_value(): void
    {
        $app = new Application($this->createContainer());

        $tester = new CommandTester($app->find('make:mutator'));

        $result = $tester->execute([
            'Mutator name' => ' customMutator ',
        ]);
        $this->assertSame(0, $result);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('Generated files', $display);
        $this->assertStringContainsString('CustomMutator.php', $display);
        $this->assertStringContainsString('CustomMutatorTest.php', $display);
    }

    public function test_uppercases_the_value_from_quetion_helper(): void
    {
        $app = new Application($this->createContainer());

        $tester = new CommandTester($app->find('make:mutator'));

        $tester->setInputs([' newMutator ']);

        $result = $tester->execute([]);

        $this->assertSame(0, $result);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('Generated files', $display);
        $this->assertStringContainsString('NewMutator.php', $display);
        $this->assertStringContainsString('NewMutator.php', $display);
    }

    private function createFileSystemMock(): MockObject
    {
        /**
         * @var FileSystem&MockObject
         */
        $fileSystemMock = $this->createMock(FileSystem::class);

        $fileSystemMock
            ->expects($this->exactly(2))
            ->method('dumpFile')
        ;

        return $fileSystemMock;
    }

    private function createContainer(): Container
    {
        return MockedContainer::createWithServices([
            FileSystem::class => $this->createFileSystemMock(...),
        ]);
    }
}
