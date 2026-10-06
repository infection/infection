<?php

declare(strict_types=1);

fwrite(STDERR, 'error output');

echo json_encode([
    'argv' => $argv,
    'server_argv' => $_SERVER['argv'],
    'foo' => getenv('FOO'),
    'stdin' => stream_get_contents(STDIN),
    'worker' => posix_getppid(),
]);

exit(3);
