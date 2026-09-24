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

namespace Infection\Tests\Mutator\Operator;

use Infection\Mutator\Operator\ContinueReturn;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ContinueReturn::class)]
final class ContinueReturnTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    /**
     * @return iterable<string, array{0: string, 1?: string}>
     */
    public static function mutationsProvider(): iterable
    {
        yield 'It replaces continue with return in a foreach followed by code' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            continue;
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            return;
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It replaces continue with return in a while loop followed by code' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    while ($item = $this->nextItem()) {
                        if ($item->isProcessed()) {
                            continue;
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    while ($item = $this->nextItem()) {
                        if ($item->isProcessed()) {
                            return;
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It replaces continue with return in a for loop followed by code' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    for ($i = 0; $i < 10; ++$i) {
                        if ($i % 2 === 0) {
                            continue;
                        }
                        $this->process($i);
                    }
                    $this->cleanUp();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    for ($i = 0; $i < 10; ++$i) {
                        if ($i % 2 === 0) {
                            return;
                        }
                        $this->process($i);
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It replaces continue with return in a do-while loop followed by code' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    do {
                        if ($this->shouldSkip()) {
                            continue;
                        }
                        $this->process();
                    } while ($this->hasNext());
                    $this->cleanUp();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    do {
                        if ($this->shouldSkip()) {
                            return;
                        }
                        $this->process();
                    } while ($this->hasNext());
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It mutates when a value-returning return follows the loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $count = 0;
                    foreach ($collection as $item) {
                        if ($item === null) {
                            continue;
                        }
                        ++$count;
                    }
                    return $count;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $count = 0;
                    foreach ($collection as $item) {
                        if ($item === null) {
                            return;
                        }
                        ++$count;
                    }
                    return $count;
                    PHP,
            ),
        ];

        yield 'It mutates when code follows the if enclosing the loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($condition) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                    }
                    $this->cleanUp();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($condition) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It mutates a continue in a loop inside a switch case when code follows the loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch ($mode) {
                        case 1:
                            foreach ($collection as $item) {
                                if ($item === null) {
                                    continue;
                                }
                                $this->process($item);
                            }
                            $this->cleanUp();
                            break;
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch ($mode) {
                        case 1:
                            foreach ($collection as $item) {
                                if ($item === null) {
                                    return;
                                }
                                $this->process($item);
                            }
                            $this->cleanUp();
                            break;
                    }
                    PHP,
            ),
        ];

        yield 'It mutates inside a closure without a return type' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $callback = function (iterable $collection) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->cleanUp();
                    };
                    $callback([]);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $callback = function (iterable $collection) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->cleanUp();
                    };
                    $callback([]);
                    PHP,
            ),
        ];

        yield 'It replaces a levelled continue with return targeting the outer loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collections as $collection) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue 2;
                            }
                            $this->process($item);
                        }
                    }
                    $this->cleanUp();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collections as $collection) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It replaces a levelled continue crossing a switch with return' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        switch ($item) {
                            case 1:
                                continue 2;
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        switch ($item) {
                            case 1:
                                return;
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It mutates in a void method with code after the loop' => [
            <<<'PHP'
                <?php

                class Walker
                {
                    public function walk(array $items): void
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->finish();
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class Walker
                {
                    public function walk(array $items): void
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->finish();
                    }
                }
                PHP,
        ];

        yield 'It mutates in a generator when a yield follows the loop' => [
            <<<'PHP'
                <?php

                class LazyFilter
                {
                    public function filter(array $items): \Generator
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            yield $item;
                        }
                        yield $this->sentinel();
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class LazyFilter
                {
                    public function filter(array $items): \Generator
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                return;
                            }
                            yield $item;
                        }
                        yield $this->sentinel();
                    }
                }
                PHP,
        ];

        yield 'It replaces continue with return null in a method with a nullable return type' => [
            <<<'PHP'
                <?php

                class Finder
                {
                    public function firstValid(array $items): ?string
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->collect($item);
                        }
                        return $this->best();
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class Finder
                {
                    public function firstValid(array $items): ?string
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                return null;
                            }
                            $this->collect($item);
                        }
                        return $this->best();
                    }
                }
                PHP,
        ];

        yield 'It replaces continue with return null in a method with a union null return type' => [
            <<<'PHP'
                <?php

                class IndexFinder
                {
                    public function firstIndex(array $items): int|null
                    {
                        foreach ($items as $index => $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->collect($index);
                        }
                        return $this->fallBackIndex();
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class IndexFinder
                {
                    public function firstIndex(array $items): int|null
                    {
                        foreach ($items as $index => $item) {
                            if ($item === null) {
                                return null;
                            }
                            $this->collect($index);
                        }
                        return $this->fallBackIndex();
                    }
                }
                PHP,
        ];

        yield 'It replaces continue with return null in a method with a mixed return type' => [
            <<<'PHP'
                <?php

                class ValuePicker
                {
                    public function pick(array $items): mixed
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->collect($item);
                        }
                        return $this->best();
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class ValuePicker
                {
                    public function pick(array $items): mixed
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                return null;
                            }
                            $this->collect($item);
                        }
                        return $this->best();
                    }
                }
                PHP,
        ];

        yield 'It replaces continue with return 0 in a method with an int return type' => [
            <<<'PHP'
                <?php

                class Counter
                {
                    public function countValid(array $items): int
                    {
                        $count = 0;
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            ++$count;
                        }
                        return $count;
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class Counter
                {
                    public function countValid(array $items): int
                    {
                        $count = 0;
                        foreach ($items as $item) {
                            if ($item === null) {
                                return 0;
                            }
                            ++$count;
                        }
                        return $count;
                    }
                }
                PHP,
        ];

        yield 'It replaces continue with return of an empty float in a method with a float return type' => [
            <<<'PHP'
                <?php

                class Summer
                {
                    public function sum(array $items): float
                    {
                        $sum = 0.0;
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $sum += $item;
                        }
                        return $sum;
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class Summer
                {
                    public function sum(array $items): float
                    {
                        $sum = 0.0;
                        foreach ($items as $item) {
                            if ($item === null) {
                                return 0.0;
                            }
                            $sum += $item;
                        }
                        return $sum;
                    }
                }
                PHP,
        ];

        yield 'It replaces continue with return of an empty string in a method with a string return type' => [
            <<<'PHP'
                <?php

                class Renderer
                {
                    public function render(array $items): string
                    {
                        $output = '_';
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $output .= $item;
                        }
                        return $output;
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class Renderer
                {
                    public function render(array $items): string
                    {
                        $output = '_';
                        foreach ($items as $item) {
                            if ($item === null) {
                                return '';
                            }
                            $output .= $item;
                        }
                        return $output;
                    }
                }
                PHP,
        ];

        yield 'It replaces continue with return false in a method with a bool return type' => [
            <<<'PHP'
                <?php

                class Validator
                {
                    public function allValid(array $items): bool
                    {
                        $valid = true;
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $valid = $valid && $this->check($item);
                        }
                        return $valid;
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class Validator
                {
                    public function allValid(array $items): bool
                    {
                        $valid = true;
                        foreach ($items as $item) {
                            if ($item === null) {
                                return false;
                            }
                            $valid = $valid && $this->check($item);
                        }
                        return $valid;
                    }
                }
                PHP,
        ];

        yield 'It replaces continue with return of an empty array in a method with an array return type' => [
            <<<'PHP'
                <?php

                class Filter
                {
                    public function filter(array $items): array
                    {
                        $valid = [];
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $valid[] = $item;
                        }
                        return $valid;
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class Filter
                {
                    public function filter(array $items): array
                    {
                        $valid = [];
                        foreach ($items as $item) {
                            if ($item === null) {
                                return [];
                            }
                            $valid[] = $item;
                        }
                        return $valid;
                    }
                }
                PHP,
        ];

        yield 'It replaces continue with return of an empty array in a non-generator method with an iterable return type' => [
            <<<'PHP'
                <?php

                class Collector
                {
                    public function collect(array $items): iterable
                    {
                        $valid = [];
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $valid[] = $item;
                        }
                        return $valid;
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class Collector
                {
                    public function collect(array $items): iterable
                    {
                        $valid = [];
                        foreach ($items as $item) {
                            if ($item === null) {
                                return [];
                            }
                            $valid[] = $item;
                        }
                        return $valid;
                    }
                }
                PHP,
        ];

        yield 'It does not mutate when nothing follows the loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            continue;
                        }
                        $this->process($item);
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate when only a bare return follows the loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            continue;
                        }
                        $this->process($item);
                    }
                    return;
                    PHP,
            ),
        ];

        yield 'It does not mutate when only return null follows the loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            continue;
                        }
                        $this->process($item);
                    }
                    return null;
                    PHP,
            ),
        ];

        yield 'It does not mutate when the same empty default follows the loop' => [
            <<<'PHP'
                <?php

                class ZeroCounter
                {
                    public function count(array $items): int
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->collect($item);
                        }
                        return 0;
                    }
                }
                PHP,
        ];

        yield 'It does not mutate a levelled continue when nothing follows the outer loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collections as $collection) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue 2;
                            }
                            $this->process($item);
                        }
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate a levelled continue exceeding the loop nesting' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            continue 3;
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It does not mutate a continue with a dynamic level' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            continue $level;
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It does not mutate a continue inside a switch case' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        switch ($item) {
                            case 1:
                                continue;
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It does not mutate a continue nested in an if inside a switch case' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        switch ($item) {
                            case 1:
                                if ($item > 1) {
                                    continue;
                                }
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It does not mutate when the loop is the last statement of a then branch with an else' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($condition) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                    } else {
                        $this->fallBack();
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate when the loop is the last statement of an elseif branch' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($first) {
                        $this->first();
                    } elseif ($second) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                    } else {
                        $this->fallBack();
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate when the loop is the last statement of a catch block with a sibling catch' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    try {
                        $this->attempt();
                    } catch (\RuntimeException $exception) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                    } catch (\LogicException $exception) {
                        $this->fallBack();
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate when the first statement after the loop is a break of an enclosing switch' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch ($mode) {
                        case 1:
                            foreach ($collection as $item) {
                                if ($item === null) {
                                    continue;
                                }
                                $this->process($item);
                            }
                            break;
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate in a method with an object return type' => [
            <<<'PHP'
                <?php

                class Builder
                {
                    public function build(array $items): \DateTimeImmutable
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->collect($item);
                        }
                        return $this->create();
                    }
                }
                PHP,
        ];

        yield 'It does not mutate in a method with a non-null union return type' => [
            <<<'PHP'
                <?php

                class Picker
                {
                    public function pick(array $items): int|string
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->collect($item);
                        }
                        return $this->best();
                    }
                }
                PHP,
        ];

        yield 'It mutates a continue inside a try block within a loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        try {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        } catch (\Throwable $exception) {
                            $this->handle($exception);
                        }
                    }
                    $this->cleanUp();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        try {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        } catch (\Throwable $exception) {
                            $this->handle($exception);
                        }
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It mutates when a throw follows the loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            continue;
                        }
                        $this->process($item);
                    }
                    throw new \RuntimeException('No valid item');
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            return;
                        }
                        $this->process($item);
                    }
                    throw new \RuntimeException('No valid item');
                    PHP,
            ),
        ];

        yield 'It replaces continue with a bare return in a generator with an iterable return type' => [
            <<<'PHP'
                <?php

                class LazyCollector
                {
                    public function collect(array $items): iterable
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            yield $item;
                        }
                        yield $this->sentinel();
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class LazyCollector
                {
                    public function collect(array $items): iterable
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                return;
                            }
                            yield $item;
                        }
                        yield $this->sentinel();
                    }
                }
                PHP,
        ];

        yield 'It replaces continue with return true in a method with a true return type' => [
            <<<'PHP'
                <?php

                class Confirmer
                {
                    public function confirm(array $items): true
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->register($item);
                        }
                        $this->flush();
                        return true;
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class Confirmer
                {
                    public function confirm(array $items): true
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                return true;
                            }
                            $this->register($item);
                        }
                        $this->flush();
                        return true;
                    }
                }
                PHP,
        ];

        // The whole pipeline skips plain functions: ReflectionVisitor does not
        // traverse Node\Stmt\Function_ (see also infection/infection#1483).
        yield 'It does not mutate in a plain function' => [
            <<<'PHP'
                <?php

                function walk_items(array $items)
                {
                    foreach ($items as $item) {
                        if ($item === null) {
                            continue;
                        }
                        process_item($item);
                    }
                    clean_up();
                }
                PHP,
        ];

        yield 'It does not mutate when only a comment follows the loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            continue;
                        }
                        $this->process($item);
                    }
                    // nothing to clean up
                    PHP,
            ),
        ];

        yield 'It does not mutate in a method with a never return type' => [
            <<<'PHP'
                <?php

                class Thrower
                {
                    public function fail(array $items): never
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->log($item);
                        }
                        throw new \RuntimeException('failed');
                    }
                }
                PHP,
        ];

        yield 'It does not mutate a levelled continue targeting an enclosing switch' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    switch ($mode) {
                        case 1:
                            while ($this->hasNext()) {
                                if ($this->shouldSkip()) {
                                    continue 2;
                                }
                                $this->process();
                            }
                            break;
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It does not mutate in a method with a static return type' => [
            <<<'PHP'
                <?php

                class FluentBuilder
                {
                    public function withItems(array $items): static
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->add($item);
                        }
                        return $this;
                    }
                }
                PHP,
        ];

        yield 'It replaces a levelled continue written as continue 1 with return' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            continue 1;
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            return;
                        }
                        $this->process($item);
                    }
                    $this->cleanUp();
                    PHP,
            ),
        ];

        yield 'It replaces continue with return false in a method with a false return type' => [
            <<<'PHP'
                <?php

                class Rejector
                {
                    public function reject(array $items): false
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->register($item);
                        }
                        $this->flush();
                        return false;
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class Rejector
                {
                    public function reject(array $items): false
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                return false;
                            }
                            $this->register($item);
                        }
                        $this->flush();
                        return false;
                    }
                }
                PHP,
        ];

        yield 'It mutates when code follows the loop inside an enclosing while loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    while ($this->hasNext()) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    while ($this->hasNext()) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
        ];

        yield 'It mutates when code follows the loop inside an enclosing do-while loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    do {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    } while ($this->hasNext());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    do {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    } while ($this->hasNext());
                    PHP,
            ),
        ];

        yield 'It mutates when code follows the loop inside an enclosing for loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    for ($i = 0; $i < 3; ++$i) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    for ($i = 0; $i < 3; ++$i) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
        ];

        yield 'It mutates when code follows the loop inside an enclosing foreach loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collections as $collection) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collections as $collection) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
        ];

        yield 'It mutates when code follows the loop inside a then branch' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($condition) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($condition) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
        ];

        yield 'It mutates when code follows the loop inside an else branch' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($condition) {
                        $this->first();
                    } else {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($condition) {
                        $this->first();
                    } else {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
        ];

        yield 'It mutates when code follows the loop inside an elseif branch' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($first) {
                        $this->first();
                    } elseif ($second) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($first) {
                        $this->first();
                    } elseif ($second) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
        ];

        yield 'It mutates when code follows the loop inside a try block' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    try {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    } catch (\Throwable $exception) {
                        $this->handle($exception);
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    try {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    } catch (\Throwable $exception) {
                        $this->handle($exception);
                    }
                    PHP,
            ),
        ];

        yield 'It mutates when code follows the loop inside a catch block' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    try {
                        $this->attempt();
                    } catch (\Throwable $exception) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    try {
                        $this->attempt();
                    } catch (\Throwable $exception) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
        ];

        yield 'It mutates when code follows the loop inside a finally block' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    try {
                        $this->attempt();
                    } finally {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    try {
                        $this->attempt();
                    } finally {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
        ];

        yield 'It mutates when code follows the loop inside a bare block' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                return;
                            }
                            $this->process($item);
                        }
                        $this->afterInnerLoop();
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate when the same empty float follows the loop' => [
            <<<'PHP'
                <?php

                class ZeroSummer
                {
                    public function sum(array $items): float
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->collect($item);
                        }
                        return 0.0;
                    }
                }
                PHP,
        ];

        yield 'It does not mutate when the same empty string follows the loop' => [
            <<<'PHP'
                <?php

                class EmptyRenderer
                {
                    public function render(array $items): string
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->collect($item);
                        }
                        return '';
                    }
                }
                PHP,
        ];

        yield 'It does not mutate when the same false constant follows the loop' => [
            <<<'PHP'
                <?php

                class AlwaysInvalid
                {
                    public function validate(array $items): bool
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->collect($item);
                        }
                        return false;
                    }
                }
                PHP,
        ];

        yield 'It does not mutate when the same empty array follows the loop' => [
            <<<'PHP'
                <?php

                class EmptyCollector
                {
                    public function collect(array $items): array
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->register($item);
                        }
                        return [];
                    }
                }
                PHP,
        ];

        // A non-empty array is not the empty default the mutant returns: unlike
        // the case above, jumping to `return [0];` is an observable change.
        yield 'It mutates when a non-empty array literal follows the loop' => [
            <<<'PHP'
                <?php

                class FallbackCollector
                {
                    public function collect(array $items): array
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->register($item);
                        }
                        return [0];
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                class FallbackCollector
                {
                    public function collect(array $items): array
                    {
                        foreach ($items as $item) {
                            if ($item === null) {
                                return [];
                            }
                            $this->register($item);
                        }
                        return [0];
                    }
                }
                PHP,
        ];

        yield 'It does not mutate when a goto follows the loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foreach ($collection as $item) {
                        if ($item === null) {
                            continue;
                        }
                        $this->process($item);
                    }
                    goto end;
                    end:
                    $this->cleanUp();
                    PHP,
            ),
        ];

        // Declare_ is a statement container findStatementList() does not recognize:
        // the successor search conservatively stops there instead of finding the
        // sibling inside the block.
        yield 'It does not mutate inside a declare block even if code follows the loop' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    declare(ticks=1) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                        $this->cleanUp();
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate when nothing follows the loop inside a closure even if code follows the closure' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $callback = function (iterable $collection) {
                        foreach ($collection as $item) {
                            if ($item === null) {
                                continue;
                            }
                            $this->process($item);
                        }
                    };
                    $callback([]);
                    PHP,
            ),
        ];
    }
}
