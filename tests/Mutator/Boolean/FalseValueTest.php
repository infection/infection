<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Tests\Mutator\AbstractMutatorTestCase;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;

/**
 * @internal
 */
final class FalseValueTest extends AbstractMutatorTestCase
{
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
            'It mutates false to true' => [
                <<<'PHP'
<?php

return false;
PHP
                ,
                <<<'PHP'
<?php

return true;
PHP
                ,
            ],
            'It does not mutate the string false to true' => [
                <<<'PHP'
<?php

return 'false';
PHP
                ,
            ],
            'It mutates all caps false to true' => [
                <<<'PHP'
<?php

return FALSE;
PHP
                ,
                <<<'PHP'
<?php

return true;
PHP
                ,
            ],
        ];
    }

    public function test_mutates_false_value()
    {
        $falseValue = new ConstFetch(new Name('false'));

        $this->assertTrue($this->mutator->shouldMutate($falseValue));
    }

    public function test_does_not_mutate_true_value()
    {
        $falseValue = new ConstFetch(new Name('true'));

        $this->assertFalse($this->mutator->shouldMutate($falseValue));
    }
}
