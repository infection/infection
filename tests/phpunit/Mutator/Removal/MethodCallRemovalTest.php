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

namespace Infection\Tests\Mutator\Removal;

use Infection\Mutator\Removal\MethodCallRemoval;
use Infection\TestFramework\Tracing\Trace\EmptyTrace;
use Infection\Testing\BaseMutatorTestCase;
use Infection\Testing\FileSystem\MockSplFileInfo;
use Infection\Testing\SingletonContainer;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use function Pipeline\take;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(MethodCallRemoval::class)]
final class MethodCallRemovalTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[]|null $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array|null $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It removes a method call without parameters' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->foo();
                    $a = 3;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It removes a method call with parameters' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $foo->bar(3, 4);
                    $a = 3;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = 3;
                    PHP,
            ),
        ];

        // TODO: Incorrect: array_pop($replyTo) must be retained because it modifies $replyTo.
        yield 'It removes a method call with an array_pop argument without assignments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->formatAddress(array_pop($replyTo));
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    PHP,
            ),
        ];

        // TODO: Incorrect: $i++ must be retained because it modifies $i.
        yield 'It removes a method call with a post-increment argument without assignments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->printIssueListElement($i++, $title, $body);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    PHP,
            ),
        ];

        // TODO: Incorrect: ++$operationPriority must be retained because it modifies $operationPriority.
        yield 'It removes a method call with a pre-increment argument without assignments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $operation->withPriority(++$operationPriority);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    PHP,
            ),
        ];

        // TODO: Incorrect: $errorsCallback() must be retained because invoking it can have side effects.
        yield 'It removes a method call with an invoked callback argument without assignments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $transformer->absolutizeErrors($errorsCallback());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    PHP,
            ),
        ];

        yield 'It remove a static method call without parameters' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    self::foo();
                    $a = 3;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It remove a static method call with parameters' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    THatClass::bar(3, 4);
                    $a = 3;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It remove a null-safe method call with parameters' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $foo?->bar(3, 4);
                    $a = 3;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It does not remove a method call that is assigned to something' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $b = $this->foo();
                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It does not remove a method call within a statement' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    if ($this->foo()) {
                        $a = 3;
                    }
                    while ($foo->foo()) {
                        $a = 3;
                    }

                    PHP,
            ),
        ];

        yield 'It does not remove a method call that is the parameter of another function or method' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = $this->foo(3, $a->bar());
                    PHP,
            ),
        ];

        yield 'It preserves an assignment in an argument when removing a method call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->checkAccess($prospect = $this->find($id));
                    return $prospect;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $prospect = $this->find($id);
                    return $prospect;
                    PHP,
            ),
        ];

        yield 'It preserves the enclosing expression of a nested assignment' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->checkOther(foo($other = 1));
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    foo($other = 1);
                    PHP,
            ),
        ];

        yield 'It preserves compound assignments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->foo($a += 1);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a += 1;
                    PHP,
            ),
        ];

        yield 'It preserves reference assignments in static calls' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    self::bar($b = &$c);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $b = &$c;
                    PHP,
            ),
        ];

        yield 'It preserves multiple assignments in one mutant in evaluation order' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check($a = first(), $b = second($a), $a += $b);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = first();
                    $b = second($a);
                    $a += $b;
                    PHP,
            ),
        ];

        yield 'It preserves chained assignments as one expression' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check($a = $b = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $a = $b = load();
                    PHP,
            ),
        ];

        yield 'It preserves destructuring assignments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check([$a, $b] = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    [$a, $b] = load();
                    PHP,
            ),
        ];

        yield 'It preserves statement comments once when retaining multiple expressions' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    // Resolve both values before continuing.
                    $this->check($a = first(), $b = second($a));
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    // Resolve both values before continuing.
                    $a = first();
                    $b = second($a);
                    PHP,
            ),
        ];

        yield 'It preserves conditional execution of an assignment' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check($ready && ($value = load()));
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $ready && ($value = load());
                    PHP,
            ),
        ];

        yield 'It retains other argument evaluations around an assignment' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check(before(), $value = load(), after());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    before();
                    $value = load();
                    after();
                    PHP,
            ),
        ];

        yield 'It preserves named argument evaluation order' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check(second: $b = first(), first: $a = second($b));
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $b = first();
                    $a = second($b);
                    PHP,
            ),
        ];

        yield 'It preserves receiver evaluation before argument assignments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->createService()->check($value = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->createService();
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It preserves an assignment in the receiver without arguments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ($service = createService())->check();
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $service = createService();
                    PHP,
            ),
        ];

        yield 'It preserves receiver and argument assignments together' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ($service = createService())->check($value = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $service = createService();
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It omits a property receiver without side effects' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $services->active->check($value = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It omits literal and read-only arguments and receivers' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->cache->set('key', $value = compute(), $other, true, null, 42, [$other], $items[0], Services::$active);
                    return $value;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $value = compute();
                    return $value;
                    PHP,
            ),
        ];

        yield 'It omits a read-only dynamic method name' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $service->{$method}('key', $value = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It omits deferred functions and first-class callables around assignments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check(fn () => $state->value = load(), $value = load(), function () { update(); }, load(...));
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It retains calls nested in argument expressions in evaluation order' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check([before()], $value = load(), $ready && after(), new Service());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    [before()];
                    $value = load();
                    $ready && after();
                    new Service();
                    PHP,
            ),
        ];

        yield 'It retains calls nested in fetches' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    createService()->active->check($items[nextIndex()], $value = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    createService()->active;
                    $items[nextIndex()];
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It retains increments and decrements around assignments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check(++$count, $value = $count, $count--);
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    ++$count;
                    $value = $count;
                    $count--;
                    PHP,
            ),
        ];

        yield 'It preserves dynamic receiver variable evaluation' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ${$name = getServiceName()}->check($value = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    ${$name = getServiceName()};
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It preserves dynamic method name evaluation before arguments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $service->{$name = getMethodName()}($value = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $name = getMethodName();
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It preserves a dynamic static class expression' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ($class = getClassName())::check($value = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $class = getClassName();
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It retains arguments when the static class is a simple variable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $class::check($value = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It omits a static property receiver without side effects' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    Services::$active->check($value = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It evaluates the receiver and dynamic method name before the arguments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    createService()->{getMethodName()}($value = load());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    createService();
                    getMethodName();
                    $value = load();
                    PHP,
            ),
        ];

        yield 'It does not remove a method call assigning a variable in its caller' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ($a = new Foo())?->bar();
                    PHP,
            ),
        ];

        yield 'It removes a method call with an assignment scoped to a closure argument' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->foo(function () {
                        $a = 1;
                    }, fn () => $b = 2);
                    $a = 3;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'

                    $a = 3;
                    PHP,
            ),
        ];

        yield 'It preserves an immediately invoked closure assigning a captured reference' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->consume((function () use (&$value) {
                        return $value = 42;
                    })());

                    return $value;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    (function () use (&$value) {
                        return $value = 42;
                    })();

                    return $value;
                    PHP,
            ),
        ];

        yield 'It preserves an immediately invoked arrow function assigning a property' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->consume((fn () => $state->value = 42)());

                    return $state->value;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    (fn () => $state->value = 42)();

                    return $state->value;
                    PHP,
            ),
        ];

        yield 'It leaves a nullsafe expression within a retained argument intact' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check($service?->load($value = load()));
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $service?->load($value = load());
                    PHP,
            ),
        ];

        yield 'It skips assignments in the arguments of a nullsafe call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $service?->check($value = load());
                    PHP,
            ),
        ];

        yield 'It skips assignments after a nullsafe method receiver' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $service?->prepare()->check($value = load());
                    PHP,
            ),
        ];

        yield 'It skips assignments after a nullsafe property receiver' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $service?->active->check($value = load());
                    PHP,
            ),
        ];

        yield 'It skips assignments after a nullsafe chain with a regular property access' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $service?->active->handler->check($value = load());
                    PHP,
            ),
        ];

        yield 'It skips assignments in a static call following a nullsafe access' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $service?->active::check($value = load());
                    PHP,
            ),
        ];

        yield 'It skips assignments after a static property in a nullsafe chain' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $service?->active::$handler->check($value = load());
                    PHP,
            ),
        ];

        yield 'It skips assignments after a nullsafe receiver with array access' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $services?->active[0]->check($value = load());
                    PHP,
            ),
        ];

        yield 'It skips assignments with unpacked arguments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check(...($items = generateItems()), last: $last = readState());
                    PHP,
            ),
        ];

        yield 'It skips assignments when creating a first-class callable' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    ($service = createService())->check(...);
                    PHP,
            ),
        ];

        yield 'It removes a method call with an immediately invoked closure without assignments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->consume((fn () => 42)());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    PHP,
            ),
        ];

        // TODO: Incorrect: creating the closure initializes $value through use (&$value), even without invoking its body.
        yield 'It removes a method call creating a first-class callable from a closure' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->consume((function () use (&$value) {
                        return $value = 42;
                    })(...));
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    PHP,
            ),
        ];

        yield 'It removes a method call with an invoked arrow function returning an uninvoked closure' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->consume((fn () => fn () => $state->value = 42)());
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    PHP,
            ),
        ];

        yield 'It does not remove a constructor call or its method call arguments' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    new PendingChain($jobs->shift(), $jobs->toArray());
                    PHP,
            ),
        ];

        yield 'It does not remove a function call' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    foo();
                    $a = 3;
                    PHP,
            ),
        ];
    }

    public function test_it_does_not_change_the_input_statement_when_preserving_assignments(): void
    {
        $container = SingletonContainer::getContainer()->withValues(
            logger: new NullLogger(),
            output: new NullOutput(),
            withUncovered: true,
        );

        $sourceFile = new MockSplFileInfo(realPath: '/path/to/virtual-test-file.php');

        $nodes = $container->getParser()->parse(
            self::wrapCodeInMethod(
                <<<'PHP'
                    $this->check($a = first(), $b = second($a));
                    PHP,
            ),
        );
        $this->assertNotNull($nodes);

        $container->getNodeTraverserFactory()
            ->createEnrichmentTraverser(
                $sourceFile,
                new EmptyTrace($sourceFile),
            )
            ->traverse($nodes)
        ;

        $statement = (new NodeFinder())->findFirstInstanceOf(
            $nodes,
            Expression::class,
        );

        $this->assertNotNull($statement);

        $originalExpression = $statement->expr;

        $mutations = take($this->mutator->mutate($statement))->toList();

        $newExpression = $statement->expr;

        $this->assertCount(1, $mutations);
        $this->assertSame($originalExpression, $newExpression);
    }
}
