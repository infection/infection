<?php

declare(strict_types=1);

namespace Infection\Configuration\Entry\Mutator;

use Webmozart\Assert\Assert;

final class ArrayItemRemovalSettings
{
    private const REMOVE_VALUES = [
        'first',
        'last',
        'all',
    ];

    private $remove;
    private $limit;

    public function __construct(string $remove, ?int $limit)
    {
        Assert::oneOf($remove, self::REMOVE_VALUES);
        Assert::nullOrGreaterThanEq($limit, 1);

        $this->remove = $remove;
        $this->limit = $limit;
    }

    public function getRemove(): string
    {
        return $this->remove;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }
}