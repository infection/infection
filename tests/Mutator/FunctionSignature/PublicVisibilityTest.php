<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\FunctionSignature;

use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class PublicVisibilityTest extends AbstractMutator
{
    public function test_changes_public_to_protected_method_visibility()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/pv-one-class.php');
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

namespace PublicVisibilityOneClass;

class Test
{
    protected function foo(int $param, $test = 1) : bool
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
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/pv-static.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
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
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_change_abstract_flag()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/pv-abstract.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

namespace PublicVisibilityAbstract;

abstract class Test
{
    protected abstract function foo(int $param, $test = 1) : bool;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_change_final_flag()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/pv-final.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
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
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    /**
     * @dataProvider blacklistedProvider
     */
    public function test_it_does_not_modify_blacklisted_functions(string $functionName, string $args = '', string $modifier = '')
    {
        $code = file_get_contents(__DIR__ . "/../../Fixtures/Autoloaded/PublicVisibility/pv-{$functionName}.php");

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace PublicVisibility{$functionName};

class Test
{
    public {$modifier}function {$functionName}($args)
    {
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_replaces_visibility_if_not_set()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/pv-not-set.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

namespace PublicVisibilityNotSet;

class Test
{
    protected function foo()
    {
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_if_interface_has_same_public_method()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/pv-same-method-interface.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

namespace Infection\Tests\Files\Autoloaded;

interface SomeInterface
{
    public function foo();
}
class Child implements SomeInterface
{
    public function foo()
    {
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_if_any_of_interfaces_has_same_public_method()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/pv-same-method-any-interface.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

namespace PublicVisibility_AnyInterface;

interface FirstInterface
{
}
interface SecondInterface
{
    public function foo();
}
interface ThirdInterface
{
}
class Child implements FirstInterface, SecondInterface, ThirdInterface
{
    public function foo()
    {
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_if_parent_abstract_has_same_public_method()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/pv-same-method-abstract.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

namespace SameAbstract;

abstract class SameAbstract
{
    protected abstract function foo();
}
class Child extends SameAbstract
{
    public function foo()
    {
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_if_parent_class_has_same_public_method()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/pv-same-method-parent.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
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
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_modify_interface_methods()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/pv-interface.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

namespace TestInterface;

interface TestInterface
{
    public function test();
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_if_grandparent_class_has_same_public_method()
    {
        $code = file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/pv-same-method-grandparent.php');

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
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
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function blacklistedProvider()
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

    protected function getMutator(): Mutator
    {
        return new PublicVisibility();
    }
}
