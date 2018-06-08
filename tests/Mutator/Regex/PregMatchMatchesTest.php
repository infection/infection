<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Regex;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class PregMatchMatchesTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider providesMutatorCases
     *
     * @param string $input
     * @param string|null $output
     */
    public function test_mutator(string $input, string $output = null)
    {
        $this->doTest($input, $output);
    }

    public function providesMutatorCases(): \Generator
    {
        yield 'It mutates ' => [
            <<<'PHP'
<?php

preg_match('/a/', 'b', $foo);
PHP
            ,
            <<<'PHP'
<?php

$foo = array();
PHP
        ];

        yield 'It does not mutate if the function is a variable' => [
            <<<'PHP'
<?php

$foo = 'preg_match';
$foo('/a/', 'b', $bar);
PHP
        ];
        yield 'It mutates if preg_match is incorrectly cased' => [
          <<<'PHP'
<?php

PreG_maTch('/a/', 'b', $foo);
PHP
            ,
            <<<'PHP'
<?php

$foo = array();
PHP
        ];
        yield 'It does not mutate if there are less than 3 arguments' => [
            <<<'PHP'
<?php

preg_match('/asdfa/', 'foo');
PHP
        ];

        yield 'It mutates correctly if the 3rd variable is a property' => [
            <<<'PHP'
<?php

preg_match('/a/', 'b', $a->b);
PHP
            ,
            <<<'PHP'
<?php

$a->b = array();
PHP
        ];

        yield 'It mutates if the return type allows it' => [
            file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/RegexMatchMatches/ReturnTypes.php'),
            <<<'PHP'
<?php

namespace RegexMatchMatches;

class ReturnTypes
{
    public function foo()
    {
        return $matches = array();
    }
}
PHP
        ];

        yield 'It does not mutate if the return type does not allow it' => [
            file_get_contents(__DIR__ . '/../../Fixtures/Autoloaded/RegexMatchMatches/ReturnNotAllowed.php'),
        ];
    }
}
