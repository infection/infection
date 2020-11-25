<?php

declare(strict_types=1);

namespace Infection\Tests\Mutator\Removal;

use Infection\Tests\Mutator\BaseMutatorTestCase;

final class ConcatOperandRemovalTest extends BaseMutatorTestCase
{
    /**
     * @dataProvider mutationsProvider
     *
     * @param string|string[] $expected
     */
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->doTest($input, $expected);
    }

    public function mutationsProvider(): iterable
    {
        yield 'Removes both operands' => [
            <<<'PHP'
<?php
'foo' . 'bar';
PHP
            ,
            [
                <<<'PHP'
<?php

'foo';
PHP
                ,
                <<<'PHP'
<?php

'bar';
PHP
            ]
        ];
    }
}
