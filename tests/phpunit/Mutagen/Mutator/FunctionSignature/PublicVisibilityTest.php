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

namespace Infection\Tests\Mutagen\Mutator\FunctionSignature;

use Generator;
use Infection\Tests\Mutagen\Mutator\AbstractMutatorTestCase;

/**
 * @group integration Requires some I/O operations
 */
final class PublicVisibilityTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider blacklistedProvider
     */
    public function test_it_does_not_modify_blacklisted_functions(string $functionName, string $args = '', string $modifier = ''): void
    {
        $code = $this->getFileContent("pv-{$functionName}.php");

        $this->doTest($code);
    }

    public function blacklistedProvider(): array
    {
        return [
            ['__construct'],
            ['__invoke'],
            ['__call', '$n, $v'],
            ['__callStatic', '$n, $v', 'static '],
            ['__get', '$n'],
            ['__set', '$n, $v'],
            ['__isset', '$n'],
            ['__unset', '$n'],
            ['__toString'],
            ['__debugInfo'],
        ];
    }

    /**
     * @dataProvider mutationsProvider
     *
     * @param string|string[] $expected
     */
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->doTest($input, $expected);
    }

    public function mutationsProvider(): Generator
    {
        yield 'It mutates public to protected' => [
            $this->getFileContent('pv-one-class.php'),
                <<<'PHP'
<?php

namespace PublicVisibilityOneClass;

class Test
{
    protected function &foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
                ,
            ];

        yield 'It does not mutate final flag' => [
            $this->getFileContent('pv-final.php'),
            <<<'PHP'
<?php

namespace PublicVisibilityFinal;

class Test
{
    protected final function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
            ,
        ];

        yield 'It mutates non abstract public to protected in an abstract class' => [
            $this->getFileContent('pv-non-abstract-in-abstract-class.php'),
            <<<'PHP'
<?php

namespace PublicVisibilityNonAbstractInAbstractClass;

abstract class Test
{
    protected function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
            ,
        ];

        yield 'It does not mutate static flag' => [
            $this->getFileContent('pv-static.php'),
        <<<'PHP'
<?php

namespace PublicVisibilityStatic;

class Test
{
    protected static function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
        ,
        ];

        yield 'It replaces visibility if not set' => [
            $this->getFileContent('pv-not-set.php'),
            <<<'PHP'
<?php

namespace PublicVisibilityNotSet;

class Test
{
    protected function foo()
    {
    }
}
PHP
            ,
        ];

        yield 'It does not mutate an interface' => [
            $this->getFileContent('pv-interface.php'),
        ];

        yield 'It does not mutate an abstract function' => [
            $this->getFileContent('pv-abstract.php'),
        ];

        yield 'It does not mutate if interface has same public method' => [
            $this->getFileContent('pv-same-method-interface.php'),
        ];

        yield 'It does not mutate if any of interfaces has same public method' => [
            $this->getFileContent('pv-same-method-any-interface.php'),
        ];

        yield 'It does not mutate if parent abstract has same public method' => [
            $this->getFileContent('pv-same-method-abstract.php'),
        ];

        yield 'It does not mutate if parent class has same public method' => [
            $this->getFileContent('pv-same-method-parent.php'),
            <<<'PHP'
<?php

namespace SameParent;

class SameParent
{
    protected function foo()
    {
    }
}
class Child extends SameParent
{
    public function foo()
    {
    }
}
PHP
            ,
        ];

        yield 'it does not mutate if grandparent class has same public method' => [
            $this->getFileContent('pv-same-method-grandparent.php'),
            <<<'PHP'
<?php

namespace SameGrandParent;

class GrandParent
{
    protected function foo()
    {
    }
}
class SameParent extends GrandParent
{
}
class Child extends SameParent
{
    public function foo()
    {
    }
}
PHP
            ,
        ];

        yield 'it does mutate non-inherited methods' => [
            $this->getFileContent('pv-non-same-method-parent.php'),
            <<<'PHP'
<?php

namespace NonSameAbstract;

abstract class NonSameAbstract
{
    public abstract function foo();
}
class Child extends NonSameAbstract
{
    public function foo()
    {
    }
    protected function bar()
    {
    }
}
PHP
        ];

        yield 'it does not mutate an anonymous class because reflection is not avalable' => [
            <<<'PHP'
<?php

function something()
{
    return new class() {
        public function anything()
        {
            return null;
        }
    };
}
PHP
        ];

        yield 'It does mutate when the parents method is protected' => [
            $this->getFileContent('pv-protected-parent.php'),
            <<<'PHP'
<?php

namespace ProtectedParent;

abstract class SameAbstract
{
    protected abstract function foo();
}
class Child extends SameAbstract
{
    protected function foo()
    {
    }
}
PHP
        ];
    }

    private function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../../../Fixtures/Autoloaded/PublicVisibility/%s', $file));
    }
}
