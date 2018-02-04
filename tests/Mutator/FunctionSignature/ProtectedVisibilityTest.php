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
        $code = <<<'CODE'
<?php

class Test
{
    protected function foo(int $param, $test = 1): bool
    {
        echo 1;
        return false;
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

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
        $code = <<<'CODE'
<?php

class Test
{
    protected static function foo(int $param, $test = 1): bool
    {
        echo 1;
        return false;
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

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
        $code = <<<'CODE'
<?php

abstract class Test
{
    abstract protected function foo(int $param, $test = 1): bool;
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

abstract class Test
{
    protected abstract function foo(int $param, $test = 1) : bool;
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_change_final_flag()
    {
        $code = <<<'CODE'
<?php

class Test
{
    final protected function foo(int $param, $test = 1): bool
    {
        echo 1;
        return false;
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

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
