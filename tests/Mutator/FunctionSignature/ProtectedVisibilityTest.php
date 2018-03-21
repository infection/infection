<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\FunctionSignature;

use Infection\Mutator\FunctionSignature\ProtectedVisibility;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class ProtectedVisibilityTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null)
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
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
    private final function foo(int $param, $test = 1) : bool
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
            <<<'PHP'
<?php

namespace ProtectedSameAbstract;

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
            ,
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

        yield 'it does not mutate if grand parent class has same protected method' => [
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
    }

    private function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../../Fixtures/Autoloaded/ProtectedVisibility/%s', $file));
    }
}
