<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 *
 * @see Mutation
 */
interface MutationInterface
{
    public function getMutator(): Mutator;

    public function getAttributes(): array;

    public function getOriginalFilePath(): string;

    public function getMutatedNodeClass(): string;

    public function getHash(): string;

    public function getOriginalFileAst(): array;

    public function isOnFunctionSignature(): bool;

    public function isCoveredByTest(): bool;

    /**
     * @return Node|Node[]|int|null Node, array of Nodes, NodeTraverser::REMOVE_NODE, NodeTraverser::STOP_TRAVERSAL, null
     */
    public function getMutatedNode();
}
