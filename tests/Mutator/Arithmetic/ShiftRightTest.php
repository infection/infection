<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class ShiftRightTest extends AbstractMutatorTestCase
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
            'It mutates shift right' => [
                <<<'PHP'
<?php

$a = 1;
$a >> 2;
PHP
                ,
                <<<'PHP'
<?php

$a = 1;
$a << 2;
PHP
                ,
            ],
            'It does not mutate shift left' => [
                <<<'PHP'
<?php

$a = 1;
$a << 2;
PHP
                ,
            ],
        ];
    }

    public function test_replaces_post_decrement(): void
    {
        $code = '<?php $a = 1; $a >> 2;';
        $mutations = $this->mutate($code);

        $expectedMutatedCode = <<<'PHP'
<?php

$a = 1;
$a << 2;
PHP;

        $this->assertSame($expectedMutatedCode, $mutations[0]);
    }
}
