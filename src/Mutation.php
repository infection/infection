<?php

declare(strict_types=1);

namespace Infection;

use Infection\Mutator\Mutator;


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
     * @var array
     */
    private $coveredTests;

    public function __construct(string $originalFilePath, Mutator $mutator, array $attributes, array $coveredTests)
    {
        $this->originalFilePath = $originalFilePath;
        $this->mutator = $mutator;
        $this->attributes = $attributes;
        $this->coveredTests = $coveredTests;
    }

    /**
     * @return Mutator
     */
    public function getMutator() : Mutator
    {
        return $this->mutator;
    }

    /**
     * @return array
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getOriginalFilePath() : string
    {
        return $this->originalFilePath;
    }

    /**
     * @return array
     */
    public function getCoveredTests(): array
    {
        return $this->coveredTests;
    }

    public function getHash() : string
    {
        $mutatorClass = get_class($this->getMutator());
        $attrs = $this->getAttributes();
        $attributeValues = [
            $attrs['startLine'],
            $attrs['endLine'],
            $attrs['startTokenPos'],
            $attrs['endTokenPos'],
            $attrs['startFilePos'],
            $attrs['startFilePos']
        ];

        $hashKeys = array_merge([$this->getOriginalFilePath(), $mutatorClass], $attributeValues);

        return md5(implode('_', $hashKeys));
    }
}
