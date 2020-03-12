<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Coverage;

use DOMNode;
use Webmozart\Assert\Assert;

final class PercentageParser
{
    private function __construct()
    {
        
    }

    public static function parsePercentage(DOMNode $node): float
    {
        $percentage = $node->getAttribute('percent');

        if (substr($percentage, -1) === '%') {
            // In PHPUnit <6 the percentage value would take the form "0.00%" in _some_ cases.
            // For example could find both with percentage and without in
            // https://github.com/maks-rafalko/tactician-domain-events/tree/1eb23434d3a833dedb6180ead75ff983ef09a2e9
            $percentage = substr($percentage, 0, -1);
        }

        if ($percentage === '') {
            return .0;
        }

        Assert::numeric($percentage);

        $percentage = (float) $percentage;

        return $percentage;
    }
}
