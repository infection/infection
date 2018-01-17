<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Tests\Mutator\AbstractMutator;

abstract class AbstractValueToNullReturnValueTest extends AbstractMutator
{
    abstract protected function getMutableNodeString(): string;

    public function test_mutates_without_typehint()
    {
        $code = <<<"CODE"
<?php

class Test
{
    function test()
    {
        return {$this->getMutableNodeString()};
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

class Test
{
    function test()
    {
        {$this->getMutableNodeString()};
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_when_scalar_return_typehint_does_not_allow_null()
    {
        $code = <<<"CODE"
<?php

class Test
{
    function test() : int
    {
        return {$this->getMutableNodeString()};
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

class Test
{
    function test() : int
    {
        return {$this->getMutableNodeString()};
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_when_scalar_return_typehint_allows_null()
    {
        $code = <<<"CODE"
<?php

class Test
{
    function test() : ?int
    {
        return {$this->getMutableNodeString()};
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

class Test
{
    function test() : ?int
    {
        {$this->getMutableNodeString()};
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_when_return_typehint_fqcn_does_not_allow_null()
    {
        $code = <<<"CODE"
<?php

class Test
{
    function test() : \DateTime
    {
        return {$this->getMutableNodeString()};
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

class Test
{
    function test() : \DateTime
    {
        return {$this->getMutableNodeString()};
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_when_return_typehint_fqcn_allows_null()
    {
        $code = <<<"CODE"
<?php

class Test
{
    function test() : ?\DateTime
    {
        return {$this->getMutableNodeString()};
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

class Test
{
    function test() : ?\DateTime
    {
        {$this->getMutableNodeString()};
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_when_function_contains_another_function_but_returns_function_call()
    {
        $code = <<<"CODE"
<?php

class Test
{
    function test() : array
    {
        \$a = function (\$element) : ?int {
            return \$element;
        };
    
        return {$this->getMutableNodeString()};
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

class Test
{
    function test() : array
    {
        \$a = function (\$element) : ?int {
            return \$element;
        };
        return {$this->getMutableNodeString()};
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_mutates_when_function_contains_another_function_but_returns_function_call_and_null_allowed()
    {
        $code = <<<"CODE"
<?php

class Test
{
    function test()
    {
        \$a = function (\$element) : ?int {
            return \$element;
        };
    
        return {$this->getMutableNodeString()};
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

class Test
{
    function test()
    {
        \$a = function (\$element) : ?int {
            return \$element;
        };
        {$this->getMutableNodeString()};
        return null;
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_not_mutates_with_not_nullable_return_typehint()
    {
        $code = <<<"CODE"
<?php

class Test
{
    function test(): bool
    {
        return {$this->getMutableNodeString()};
    }
}
CODE;
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<"CODE"
<?php

class Test
{
    function test() : bool
    {
        return {$this->getMutableNodeString()};
    }
}
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_it_does_not_mutate_a_function_outside_a_class()
    {
        $code = <<<"CODE"
<?php

function test()
{
    return 1;
}
CODE;

        $mutatedCode = $this->mutate($code);
        $this->assertSame($code, $mutatedCode);
    }
}
