<?php

declare(strict_types=1);

namespace Infection\Configuration\Mutator;

use Webmozart\Assert\Assert;
use function fnmatch;
use function in_array;
use const FNM_NOESCAPE;

final class Ignore
{
    private $items;

    /**
     * @param string[] $items
     */
    public function __construct(array $items)
    {
        Assert::allString($items);

        $this->items = $items;
    }

    public function isIgnored(string $class, string $method, ?int $lineNumber = null): bool
    {
        if (in_array($class, $this->items)) {
            return true;
        }

        if (in_array($class . '::' . $method, $this->items)) {
            return true;
        }

        foreach ($this->items as $ignorePattern) {
            if (fnmatch($ignorePattern, $class, FNM_NOESCAPE)
                || fnmatch($ignorePattern, $class . '::' . $method, FNM_NOESCAPE)
                || ($lineNumber !== null && fnmatch($ignorePattern, $class . '::' . $method . '::' . $lineNumber, FNM_NOESCAPE))
            ) {
                return true;
            }
        }

        return false;
    }
}
