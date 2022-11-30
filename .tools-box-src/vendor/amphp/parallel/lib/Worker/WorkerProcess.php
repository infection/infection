<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

final class WorkerProcess extends TaskWorker
{
    const SCRIPT_PATH = __DIR__ . "/Internal/worker-process.php";
    public function __construct(string $envClassName = BasicEnvironment::class, array $env = [], string $binary = null, string $bootstrapPath = null)
    {
        $script = [self::SCRIPT_PATH, $envClassName];
        if ($bootstrapPath !== null) {
            $script[] = $bootstrapPath;
        }
        parent::__construct(new Internal\WorkerProcess($script, $env, $binary));
    }
}
