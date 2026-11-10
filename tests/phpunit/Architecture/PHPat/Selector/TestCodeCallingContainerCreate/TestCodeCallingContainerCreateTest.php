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

namespace Infection\Tests\Architecture\PHPat\Selector\TestCodeCallingContainerCreate;

use Infection\Testing\SingletonContainer;
use Infection\Tests\Architecture\PHPat\Selector\InfectionSelector;
use Infection\Tests\Architecture\PHPat\Selector\SelectorTestCase;
use Infection\Tests\Architecture\PHPat\Selector\TestCodeCallingContainerCreate;
use Infection\Tests\Architecture\PHPat\Selector\TestCodeCallingContainerCreate\Fixtures\AbstractContainerUsingTestCase;
use Infection\Tests\Architecture\PHPat\Selector\TestCodeCallingContainerCreate\Fixtures\ContainerUsingTestHelper;
use Infection\Tests\Architecture\PHPat\Selector\TestCodeCallingContainerCreate\Fixtures\ContainerUsingTrait;
use Infection\Tests\Architecture\PHPat\Selector\TestCodeCallingContainerCreate\Fixtures\CreatesContainer;
use Infection\Tests\Command\RunCommandTest;
use Infection\Tests\Container\ContainerTest;
use Infection\Tests\MockedContainer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(TestCodeCallingContainerCreate::class)]
final class TestCodeCallingContainerCreateTest extends SelectorTestCase
{
    /**
     * @param class-string $className
     */
    #[DataProvider('classProvider')]
    public function test_it_matches_test_code_calling_container_create(string $className, bool $expected): void
    {
        $selector = InfectionSelector::testCodeCallingContainerCreate();
        $actual = $selector->matches($this->createClassReflection($className));

        $this->assertSame($expected, $actual);
    }

    public static function classProvider(): iterable
    {
        yield 'production container tests' => [ContainerTest::class, true];

        yield 'concrete test helper with an aliased call' => [ContainerUsingTestHelper::class, true];

        yield 'abstract test helper' => [AbstractContainerUsingTestCase::class, true];

        yield 'test helper trait' => [CreatesContainer::class, true];

        yield 'wrapper whose trait is checked separately' => [ContainerUsingTrait::class, false];

        yield 'test using the shared container' => [RunCommandTest::class, false];

        yield 'test helper using the constructor' => [MockedContainer::class, false];

        yield 'production code creating the shared container' => [SingletonContainer::class, false];
    }
}
