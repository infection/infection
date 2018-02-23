<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;

class TrueValueTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new TrueValue();
    }

    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null)
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): array
    {
        return [
            'It mutates true to false' => [
                <<<'PHP'
<?php

return true;
PHP
                ,
                <<<'PHP'
<?php

return false;
PHP
                ,
            ],
            'It does not mutate the string true to false' => [
                <<<'PHP'
<?php

return 'true';
PHP
                ,
            ],
            'It mutates all caps true to false' => [
                <<<'PHP'
<?php

return TRUE;
PHP
                ,
                <<<'PHP'
<?php

return false;
PHP
                ,
            ],
        ];
    }

    public function test_mutates_true_value()
    {
        $falseValue = new ConstFetch(new Name('true'));

        $this->assertTrue($this->mutator->shouldMutate($falseValue));
    }

    public function test_does_not_mutate_false_value()
    {
        $trueValue = new ConstFetch(new Name('false'));

        $this->assertFalse($this->mutator->shouldMutate($trueValue));
    }
}
