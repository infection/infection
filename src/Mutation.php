<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection;

use Infection\Mutator\Mutator;
use PhpParser\Node;

class Mutation
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

    public function __construct(string $originalFilePath, array $originalFileAst, Mutator $mutator, array $attributes, string $mutatedNodeClass)
    {
        $this->originalFilePath = $originalFilePath;
        $this->originalFileAst = $originalFileAst;
        $this->mutator = $mutator;
        $this->attributes = $attributes;
        $this->mutatedNodeClass = $mutatedNodeClass;
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
        $mutatorClass = get_class($this->getMutator());
        $attributes = $this->getAttributes();
        $attributeValues = [
            $attributes['startLine'],
            $attributes['endLine'],
            $attributes['startTokenPos'],
            $attributes['endTokenPos'],
            $attributes['startFilePos'],
            $attributes['endFilePos'],
        ];

        $hashKeys = array_merge([$this->getOriginalFilePath(), $mutatorClass], $attributeValues);

        return md5(implode('_', $hashKeys));
    }

    public function getOriginalFileAst(): array
    {
        return $this->originalFileAst;
    }
}
