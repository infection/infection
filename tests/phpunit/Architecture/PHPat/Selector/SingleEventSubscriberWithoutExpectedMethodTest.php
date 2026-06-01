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

namespace Infection\Tests\Architecture\PHPat\Selector;

use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitectureTest\Fixtures\CompleteEventSubscriber;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitectureTest\Fixtures\InvalidMethodEventSubscriber;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitectureTest\Fixtures\InvalidParameterCountEventSubscriber;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitectureTest\Fixtures\InvalidParameterNameEventSubscriber;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitectureTest\Fixtures\InvalidParameterTypeEventSubscriber;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitectureTest\Fixtures\InvalidReturnTypeEventSubscriber;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitectureTest\Fixtures\InvalidStaticMethodEventSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(SingleEventSubscriberWithoutExpectedMethod::class)]
final class SingleEventSubscriberWithoutExpectedMethodTest extends EventSelectorTestCase
{
    private SingleEventSubscriberWithoutExpectedMethod $selector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->selector = new SingleEventSubscriberWithoutExpectedMethod(
            $this->eventArchitecture,
        );
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('classProvider')]
    public function test_it_matches_expected_classes(
        string $className,
        bool $expected,
    ): void {
        $this->assertSelectorMatches(
            $this->selector,
            $className,
            $expected,
        );
    }

    public static function classProvider(): iterable
    {
        yield 'single-event subscriber without expected method' => [InvalidMethodEventSubscriber::class, true];

        yield 'single-event subscriber without expected parameter count' => [InvalidParameterCountEventSubscriber::class, true];

        yield 'single-event subscriber without expected parameter name' => [InvalidParameterNameEventSubscriber::class, true];

        yield 'single-event subscriber without expected parameter type' => [InvalidParameterTypeEventSubscriber::class, true];

        yield 'single-event subscriber without expected return type' => [InvalidReturnTypeEventSubscriber::class, true];

        yield 'single-event subscriber with static expected method' => [InvalidStaticMethodEventSubscriber::class, true];

        yield 'single-event subscriber with expected method' => [CompleteEventSubscriber::class, false];
    }
}
