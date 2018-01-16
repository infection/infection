<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalNot;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;

class LogicalNotTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new LogicalNot();
    }

    public function test_it_mutates_logical_not()
    {
        $expr = new BooleanNot(new ConstFetch(new Name('false')));

        $this->assertTrue($this->mutator->shouldMutate($expr));
    }

    public function test_it_does_not_mutates_doubled_logical_not()
    {
        $expr = new BooleanNot(
            new BooleanNot(new ConstFetch(new Name('false')))
        );

        $this->assertFalse($this->mutator->shouldMutate($expr));
    }

    public function test_replaces_logical_not_with_empty_string()
    {
        $code = '<?php return !mt_rand();';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

return mt_rand();
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_does_not_replace_doubled_logical_not_with_empty_string()
    {
        $code = '<?php return !!false;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

return !!false;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
