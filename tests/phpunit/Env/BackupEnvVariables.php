<?php

declare(strict_types=1);

namespace Infection\Tests\Env;

use Webmozart\Assert\Assert;

trait BackupEnvVariables
{
    /**
     * @var EnvBackup
     */
    private $snapshot;

    private function createEnvBackup(): void
    {
        $this->snapshot = EnvBackup::createSnapshot();
    }

    private function restoreEnvBackup(): void
    {
        $value = $this->snapshot;

        Assert::notNull(
            $value,
            'Attempted to restore a backup but no backup has been created'
        );

        $value->restore();
    }
}
