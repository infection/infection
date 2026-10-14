<?php

declare(strict_types=1);

// The grandchild inherits the output descriptors.
$grandchild = proc_open(['sleep', '60'], [], $pipes);

echo proc_get_status($grandchild)['pid'];

if (isset($argv[1])) {
    sleep(60);
}
