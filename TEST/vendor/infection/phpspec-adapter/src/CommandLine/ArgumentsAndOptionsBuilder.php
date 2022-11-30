<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\CommandLine;

use function array_filter;
use function array_merge;
use function explode;
final class ArgumentsAndOptionsBuilder
{
    public function build(string $configPath, string $extraOptions) : array
    {
        $options = array_merge(['run', '--config', $configPath, '--no-ansi', '--format=tap', '--stop-on-failure'], explode(' ', $extraOptions));
        return array_filter($options);
    }
}
