<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests;

use Infection\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Util\MutatorConfig;
use PHPUnit\Framework\TestCase;

class MutationTest extends TestCase
{
    public function test_it_correctly_generates_hash()
    {
        $mutator = new Plus(new MutatorConfig([]));
        $attributes = [
            'startLine' => 3,
            'endLine' => 5,
            'startTokenPos' => 21,
            'endTokenPos' => 31,
            'startFilePos' => 43,
            'endFilePos' => 53,
        ];

        $mutation = new Mutation(
            '/abc.php',
            [],
            $mutator,
            $attributes,
            'Interface_',
            false
        );

        $this->assertSame('5f52c44bcebde86a7ee79d0080c0e12a', $mutation->getHash());
    }

    public function test_it_correctly_sets_is_on_function_signature()
    {
        $mutator = new Plus(new MutatorConfig([]));
        $attributes = [
            'startLine' => 3,
            'endLine' => 5,
            'startTokenPos' => 21,
            'endTokenPos' => 31,
            'startFilePos' => 43,
            'endFilePos' => 53,
        ];

        $mutation = new Mutation(
            '/abc.php',
            [],
            $mutator,
            $attributes,
            'Interface_',
            false
        );

        $this->assertFalse($mutation->isOnFunctionSignature());
    }

    public function test_it_correctly_sets_original_file_ast()
    {
        $mutator = new Plus(new MutatorConfig([]));
        $attributes = [
            'startLine' => 3,
            'endLine' => 5,
            'startTokenPos' => 21,
            'endTokenPos' => 31,
            'startFilePos' => 43,
            'endFilePos' => 53,
        ];
        $fileAst = ['file' => 'ast'];

        $mutation = new Mutation(
            '/abc.php',
            $fileAst,
            $mutator,
            $attributes,
            'Interface_',
            false
        );

        $this->assertSame($fileAst, $mutation->getOriginalFileAst());
    }
}
