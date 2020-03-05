<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Env;

use function getenv;
use PHPUnit\Framework\TestCase;
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
