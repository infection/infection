<?php

declare(strict_types=1);

posix_kill(posix_getppid(), SIGKILL);
