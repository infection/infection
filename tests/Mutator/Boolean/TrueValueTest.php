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
final class TrueValueTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
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

    public function test_mutates_true_value(): void
    {
        $falseValue = new ConstFetch(new Name('true'));

        $this->assertTrue($this->mutator->shouldMutate($falseValue));
    }

    public function test_does_not_mutate_false_value(): void
    {
        $trueValue = new ConstFetch(new Name('false'));

        $this->assertFalse($this->mutator->shouldMutate($trueValue));
    }
}
