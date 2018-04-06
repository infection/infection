<?php

declare(strict_types=1);

namespace Infection\Tests\Mutator\Cast;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class CastFloatTest extends AbstractMutatorTestCase
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
        yield 'It removes casting to float' => [
            <<<'PHP'
<?php

(float) '1.1';
(double) '1.1';
(real) '1.1';
PHP
            ,
            <<<'PHP'
<?php

'1.1';
'1.1';
'1.1';
PHP
            ,
        ];
    }
}