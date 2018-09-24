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
 */
final class Mutation implements MutationInterface
{
    /**
     * @var Mutator
     */
    private $mutator;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var string
     */
    private $originalFilePath;

    /**
     * @var Node[]
     */
    private $originalFileAst;

    /**
     * @var string
     */
    private $mutatedNodeClass;

    /**
     * @var bool
     */
    private $isOnFunctionSignature;

    /**
     * @var bool
     */
    private $isCoveredByTest;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var Node
     */
    private $mutatedNode;

    /**
     * @var int
     */
    private $mutationByMutatorIndex;

    public function __construct(
        string $originalFilePath,
        array $originalFileAst,
        Mutator $mutator,
        array $attributes,
        string $mutatedNodeClass,
        bool $isOnFunctionSignature,
        bool $isCoveredByTest,
        Node $mutatedNode,
        int $mutationByMutatorIndex
    ) {
        $this->originalFilePath = $originalFilePath;
        $this->originalFileAst = $originalFileAst;
        $this->mutator = $mutator;
        $this->attributes = $attributes;
        $this->mutatedNodeClass = $mutatedNodeClass;
        $this->isOnFunctionSignature = $isOnFunctionSignature;
        $this->isCoveredByTest = $isCoveredByTest;
        $this->mutatedNode = $mutatedNode;
        $this->mutationByMutatorIndex = $mutationByMutatorIndex;
    }

    public function getMutator(): Mutator
    {
        return $this->mutator;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getOriginalFilePath(): string
    {
        return $this->originalFilePath;
    }

    public function getMutatedNodeClass(): string
    {
        return $this->mutatedNodeClass;
    }

    public function getHash(): string
    {
        if (!isset($this->hash)) {
            $mutatorClass = \get_class($this->getMutator());
            $attributes = $this->getAttributes();
            $attributeValues = [
                $mutatorClass,
                $attributes['startLine'],
                $attributes['endLine'],
                $attributes['startTokenPos'],
                $attributes['endTokenPos'],
                $attributes['startFilePos'],
                $attributes['endFilePos'],
            ];

            $hashKeys = array_merge(
                [$this->getOriginalFilePath(), $mutatorClass, $this->mutationByMutatorIndex],
                $attributeValues
            );

            $this->hash = md5(implode('_', $hashKeys));
        }

        return $this->hash;
    }

    public function getOriginalFileAst(): array
    {
        return $this->originalFileAst;
    }

    public function isOnFunctionSignature(): bool
    {
        return $this->isOnFunctionSignature;
    }

    public function isCoveredByTest(): bool
    {
        return $this->isCoveredByTest;
    }

    public function getMutatedNode(): Node
    {
        return $this->mutatedNode;
    }
}
