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
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MutationTest extends TestCase
{
    public function test_it_correctly_generates_hash(): void
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
            false,
            true,
            new Node\Scalar\LNumber(1),
            0
        );

        $this->assertSame('2930c05082a35248987760a81b9f9a08', $mutation->getHash());
    }

    public function test_it_correctly_sets_is_on_function_signature(): void
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
            false,
            true,
            new Node\Scalar\LNumber(1),
            0
        );

        $this->assertFalse($mutation->isOnFunctionSignature());
    }

    public function test_it_correctly_sets_original_file_ast(): void
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
            false,
            true,
            new Node\Scalar\LNumber(1),
            0
        );

        $this->assertSame($fileAst, $mutation->getOriginalFileAst());
    }
}
