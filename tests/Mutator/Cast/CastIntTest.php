<?php

declare(strict_types=1);

namespace Infection\Tests\Mutator\Cast;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class CastIntTest extends AbstractMutatorTestCase
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
        yield 'It removes casting to int' => [
            <<<'PHP'
<?php

(int) 1.0;
PHP
            ,
            <<<'PHP'
<?php

1.0;
PHP
            ,
        ];
    }
}