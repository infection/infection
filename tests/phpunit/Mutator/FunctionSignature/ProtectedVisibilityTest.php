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

namespace Infection\Tests\Mutator\FunctionSignature;

use Generator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @group integration Requires some I/O operations
 */
final class ProtectedVisibilityTest extends AbstractMutatorTestCase
{
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
        yield 'It mutates protected to private' => [
            $this->getFileContent('pv-one-class.php'),
            <<<'PHP'
<?php

namespace ProtectedVisibilityOneClass;

class Test
{
    private function foo(int $param, $test = 1) : bool
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

namespace ProtectedVisibilityFinal;

class Test
{
    private final function &foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
            ,
        ];

        yield 'It does not mutate abstract protected to private' => [
            $this->getFileContent('pv-abstract.php'),
        ];

        yield 'It does mutate not abstract protected to private in an abstract class' => [
            $this->getFileContent('pv-abstract-class-protected-method.php'),
            <<<'PHP'
<?php

namespace ProtectedVisibilityAbstractClassProtectedMethod;

abstract class Test
{
    private function foo(int $param, $test = 1) : bool
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

namespace ProtectedVisibilityStatic;

class Test
{
    private static function foo(int $param, $test = 1) : bool
    {
        echo 1;
        return false;
    }
}
PHP
            ,
        ];

        yield 'It does not mutate if parent abstract has same protected method' => [
            $this->getFileContent('pv-same-method-abstract.php'),
        ];

        yield 'It does not mutate if parent class has same protected method' => [
            $this->getFileContent('pv-same-method-parent.php'),
            <<<'PHP'
<?php

namespace ProtectedSameParent;

class SameParent
{
    private function foo()
    {
    }
}
class Child extends SameParent
{
    protected function foo()
    {
    }
}
PHP
            ,
        ];

        yield 'It does not mutate if grand parent class has same protected method' => [
            $this->getFileContent('pv-same-method-grandparent.php'),
            <<<'PHP'
<?php

namespace ProtectedSameGrandParent;

class SameGrandParent
{
    private function foo()
    {
    }
}
class SameParent extends SameGrandParent
{
}
class Child extends SameParent
{
    protected function foo()
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

namespace ProtectedNonSameAbstract;

abstract class ProtectedNonSameAbstract
{
    protected abstract function foo();
}
class Child extends ProtectedNonSameAbstract
{
    protected function foo()
    {
    }
    private function bar()
    {
    }
}
PHP
        ];

        yield 'it mutates an anonymous class' => [
            <<<'PHP'
<?php

function something()
{
    return new class
    {
        protected function anything()
        {
            return null;
        }
    };
}
PHP
            ,
            <<<'PHP'
<?php

function something()
{
    return new class
    {
        private function anything()
        {
            return null;
        }
    };
}
PHP
        ];
    }

    private function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../../Fixtures/Autoloaded/ProtectedVisibility/%s', $file));
    }
}
