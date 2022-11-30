<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

use _HumbugBoxb47773b41c19\Amp\Parallel\Context\Parallel;
final class WorkerParallel extends TaskWorker
{
    const SCRIPT_PATH = __DIR__ . "/Internal/worker-process.php";
    public function __construct(string $envClassName = BasicEnvironment::class, string $bootstrapPath = null)
    {
        $script = [self::SCRIPT_PATH, $envClassName];
        if ($bootstrapPath !== null) {
            $script[] = $bootstrapPath;
        }
        parent::__construct(new Parallel($script));
    }
}
