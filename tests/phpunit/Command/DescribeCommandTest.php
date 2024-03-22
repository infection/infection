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

use Infection\Console\Application;
use Infection\Tests\SingletonContainer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(Infection\Command\DescribeCommand::class)]
final class DescribeCommandTest extends TestCase
{
    public function test_it_describes(): void
    {
        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('describe'));

        $result = $tester->execute([
            'Mutator name' => 'Yield_',
        ]);
        $this->assertSame(0, $result);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('Mutator Category: semanticReduction', $display);
        $this->assertStringContainsString('Description:', $display);
    }

    public function test_it_described_the_remedy(): void
    {
        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('describe'));

        $result = $tester->execute([
            'Mutator name' => 'Yield_',
        ]);
        $this->assertSame(0, $result);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('This mutation highlights the reliance of the side-effect', $display);
    }

    public function test_it_can_describe_a_mutator_when_asked_for_a_name(): void
    {
        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('describe'));

        $tester->setInputs(['FalseValue']);
        $result = $tester->execute([]);
        $this->assertSame(0, $result);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('Description:', $display);
    }

    public function test_it_errors_when_mutator_does_not_exist(): void
    {
        $app = new Application(SingletonContainer::getContainer());

        $tester = new CommandTester($app->find('describe'));

        $result = $tester->execute([
            'Mutator name' => 'FooBar',
        ]);
        $this->assertSame(1, $result);

        $display = $tester->getDisplay();

        $this->assertStringContainsString('The FooBar mutator does not exist', $display);
    }
}
