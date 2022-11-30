<?php

namespace _HumbugBoxb47773b41c19\Amp\Process;

const BIN_DIR = __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'bin';
const IS_WINDOWS = (\PHP_OS & "\xdf\xdf\xdf") === 'WIN';
if (!\function_exists(__NAMESPACE__ . '\\escapeArguments')) {
    if (IS_WINDOWS) {
        function escapeArguments(string $arg) : string
        {
            return '"' . \preg_replace_callback('(\\\\*("|$))', function (array $m) : string {
                return \str_repeat('\\', \strlen($m[0])) . $m[0];
            }, $arg) . '"';
        }
    } else {
        function escapeArguments(string $arg) : string
        {
            return \escapeshellarg($arg);
        }
    }
}
