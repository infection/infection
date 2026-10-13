<?php

/**
 * Writes what a script would report, then stops the worker without a result. The output explains the
 * stop, so it has to reach the caller together with the exit code of a worker that is gone.
 */

declare(strict_types=1);

fwrite(STDOUT, 'the output of a script that stopped its worker');
fwrite(STDERR, 'the error output of a script that stopped its worker');

// The buffers of a stream reach the pipe on a flush, not when this process dies of a signal.
fflush(STDOUT);
fflush(STDERR);

posix_kill(posix_getppid(), SIGKILL);
