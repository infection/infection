<?php

declare(strict_types=1);

namespace Infection\Telemetry\Tracing;

use function array_map;
use function implode;
use function sprintf;

enum RootScopes: string
{
    case INITIAL_TEST_SUITE = 'initial_test_suite';
    case INITIAL_STATIC_ANALYSIS = 'initial_static_analysis';
    case MUTATION_GENERATION = 'mutation_generation';
    case MUTATION_ANALYSIS = 'mutation_analysis';
    case FILE = 'file';

    public static function getQuotedListOfValues(): string
    {
        return sprintf(
            '"%s"',
            implode(
                '", "',
                array_map(
                    fn (self $value) => $value->value,
                    self::cases(),
                ),
            )
        );
    }
}
