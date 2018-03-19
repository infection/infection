<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Value;

use Infection\Mutator\Mutator;
use Infection\Mutator\Value\String_;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class StringTest extends AbstractMutatorTestCase
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
            'It creates empty strings' => [
                <<<'PHP'
<?php

$a = 'asdf';
PHP
                ,
                <<<'PHP'
<?php

$a = '';
PHP
                ,
            ],
            'It also changes double quoted strings' => [
                <<<'PHP'
<?php

$a = "asdf";
PHP
                ,
                <<<'PHP'
<?php

$a = '';
PHP
                ,
            ],
            'It does not change already empty strings' => [
                <<<'PHP'
<?php

$a = "";
$b = '';
PHP
                ,
            ],
        ];
    }

    protected function getMutator(): Mutator
    {
        return new String_();
    }
}
