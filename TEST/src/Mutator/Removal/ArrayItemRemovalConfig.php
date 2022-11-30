<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Removal;

use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorConfig;
use const PHP_INT_MAX;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class ArrayItemRemovalConfig implements MutatorConfig
{
    private const REMOVE_VALUES = ['first', 'last', 'all'];
    private string $remove;
    private int $limit;
    public function __construct(array $settings)
    {
        $this->remove = $settings['remove'] ?? 'first';
        $this->limit = $settings['limit'] ?? PHP_INT_MAX;
        Assert::oneOf($this->remove, self::REMOVE_VALUES);
        Assert::integer($this->limit, 'Expected the limit to be an integer. Got "%s" instead');
        Assert::greaterThanEq($this->limit, 1, 'Expected the limit to be greater or equal than 1. Got "%s" instead');
    }
    /**
    @psalm-mutation-free
    */
    public function getRemove() : string
    {
        return $this->remove;
    }
    /**
    @psalm-mutation-free
    */
    public function getLimit() : int
    {
        return $this->limit;
    }
}
