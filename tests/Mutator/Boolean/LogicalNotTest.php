<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Tests\Mutator\AbstractMutatorTestCase;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;

/**
 * @internal
 */
final class LogicalNotTest extends AbstractMutatorTestCase
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
            'It removes logical not' => [
                <<<'PHP'
<?php

return !false;
PHP
                ,
                <<<'PHP'
<?php

return false;
PHP
                ,
            ],
            'It does not remove double logical not' => [
                <<<'PHP'
<?php

return !!false;
PHP
                ,
            ],
        ];
    }

    public function test_it_mutates_logical_not(): void
    {
        $expr = new BooleanNot(new ConstFetch(new Name('false')));

        $this->assertTrue($this->mutator->shouldMutate($expr));
    }

    public function test_it_does_not_mutates_doubled_logical_not(): void
    {
        $expr = new BooleanNot(
            new BooleanNot(new ConstFetch(new Name('false')))
        );

        $this->assertFalse($this->mutator->shouldMutate($expr));
    }
}
