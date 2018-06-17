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
final class PregMatchSwapCaretTest extends AbstractMutatorTestCase
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
        yield 'It mutates correctly adding caret when provided with a string' => [
            <<<'PHP'
<?php

preg_match('#some-regexp#ig', 'irrelevant');
PHP
            ,
            <<<'PHP'
<?php

preg_match('#^some-regexp#ig', 'irrelevant');
PHP
        ];

        yield 'It mutates correctly removing caret when provided with a string' => [
            <<<'PHP'
<?php

preg_match('~^some-regexp~ig', 'irrelevant');
PHP
            ,
            <<<'PHP'
<?php

preg_match('~some-regexp~ig', 'irrelevant');
PHP
        ];
    }
}