<?php

declare(strict_types=1);

namespace Infection\Tests\Env;

use PHPUnit\Framework\TestCase;
use function getenv;
use function Safe\putenv;

final class EnvBackupTest extends TestCase
{
    public function test_it_can_backup_and_restore_environment_variables(): void
    {
        putenv('BEFORE_SNAPSHOT_0=initialValue0');
        putenv('BEFORE_SNAPSHOT_1=initialValue1');
        putenv('BEFORE_SNAPSHOT_2=initialValue2');

        $initialEnvironmentVariables = getenv();

        $snapshot = EnvBackup::createSnapshot();

        putenv('BEFORE_SNAPSHOT_0=newValue0');
        putenv('BEFORE_SNAPSHOT_1=');
        putenv('BEFORE_SNAPSHOT_2');
        putenv('AFTER_SNAPSHOT=value');

        $snapshot->restore();

        $this->assertSame('initialValue0', getenv('BEFORE_SNAPSHOT_0'));
        $this->assertSame('initialValue1', getenv('BEFORE_SNAPSHOT_1'));
        $this->assertSame('initialValue2', getenv('BEFORE_SNAPSHOT_2'));
        $this->assertFalse(getenv('AFTER_SNAPSHOT'));
        $this->assertSame($initialEnvironmentVariables, getenv());
    }
}
