<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\FunctionSignature;

use Infection\Mutator\FunctionSignature\ProtectedVisibility;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class ProtectedVisibilityTest extends AbstractMutator
{
    public function test_changes_protected_to_private_method_visibility()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/ProtectedVisibility/pv-one-class.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
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
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_change_static_flag()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/ProtectedVisibility/pv-static.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
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
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_change_abstract_protected_function()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/ProtectedVisibility/pv-abstract.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

namespace ProtectedVisibilityAbstract;

abstract class Test
{
    protected abstract function foo(int $param, $test = 1) : bool;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_if_parent_abstract_has_same_protected_method()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/ProtectedVisibility/pv-same-method-abstract.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
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
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_if_parent_class_has_same_protected_method()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/ProtectedVisibility/pv-same-method-parent.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
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
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_if_grand_parent_class_has_same_protected_method()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/ProtectedVisibility/pv-same-method-grandparent.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
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
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_change_final_flag()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/ProtectedVisibility/pv-final.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
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
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator(): Mutator
    {
        return new ProtectedVisibility();
    }
}
