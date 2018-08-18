<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Tests\Mutator\AbstractMutatorTestCase;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Return_;

/**
 * @internal
 */
final class IntegerNegationTest extends AbstractMutatorTestCase
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
            'It mutates negative int return to positive' => [
                <<<'PHP'
<?php

return -1;
return -2;
PHP
                ,
                <<<'PHP'
<?php

return 1;
return 2;
PHP
                ,
            ],
            'It mutates positive int return to negative' => [
                <<<'PHP'
<?php

return 1;
return 2;
PHP
                ,
                <<<'PHP'
<?php

return -1;
return -2;
PHP
                ,
            ],
            'It does not mutate int zero' => [
                <<<'PHP'
<?php

return 0;
PHP
                ,
            ],
            'It does not mutate floats' => [
                <<<'PHP'
<?php

return 1.0;
PHP
                ,
            ],
        ];
    }

    public function test_it_does_not_mutate_zero(): void
    {
        $node = new Return_(new LNumber(0));
        $this->assertFalse($this->getMutator()->shouldMutate($node));
    }
}
