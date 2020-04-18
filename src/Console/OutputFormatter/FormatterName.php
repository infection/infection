<?php

declare(strict_types=1);

namespace Infection\Console\OutputFormatter;

final class FormatterName
{
    public const DOT = 'dot';
    public const PROGRESS = 'progress';

    public const ALL = [
        self::DOT,
        self::PROGRESS,
    ];

    private function __construct()
    {
    }
}
