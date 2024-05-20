<?php

declare(strict_types=1);

namespace App\Tests\Mutator;

use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Mutator\__Name__;

#[CoversClass(__Name__::class)]
final class __Name__Test extends BaseMutatorTestCase
{
    protected function getTestedMutatorClassName(): string
    {
        return __Name__::class;
    }

    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->doTest($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        // TODO: write original and mutated code in each cases
        // TODO: if you want to test that the code is not mutated, just leave one element (source code) in the array
        // TODO: if mutator produces N mutants, the second array item must be an array of mutated codes

        yield 'It mutates a simple case' => [
            <<<'PHP'
                <?php

                $a = 10 + 3;
                PHP
            ,
            <<<'PHP'
                <?php

                $a = 10 - 3;
                PHP
            ,
        ];

//        yield 'It does not mutate other cases' => [
//            <<<'PHP'
//                <?php
//
//                $a = 10 * 3;
//                PHP
//            ,
//        ];

//        yield 'It produces N mutations' => [
//            <<<'PHP'
//                <?php
//
//                $a = 10 - 3;
//                PHP
//            ,
//            [
//                <<<'PHP'
//                <?php
//
//                $a = 10 - 3;
//                PHP,
//                <<<'PHP'
//                <?php
//
//                $a = 10 - -3;
//                PHP
//            ]
//        ];
    }
}
