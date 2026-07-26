<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework;

use Infection\FileSystem\Finder\ComposerExecutableFinder;
use Infection\Process\CompletedProcess;
use Infection\Process\ShellCommandLineExecutor;
use Infection\TestFramework\AdapterInstaller;
use Infection\TestFramework\TestFrameworkTypes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdapterInstaller::class)]
#[Group('integration')]
final class AdapterInstallerTest extends TestCase
{
    public function test_it_installs_an_adapter_with_composer(): void
    {
        $composerExecutableFinder = $this->createStub(ComposerExecutableFinder::class);
        $composerExecutableFinder
            ->method('find')
            ->willReturn(['/path/to/composer']);

        $shellCommandLineExecutor = $this->createMock(ShellCommandLineExecutor::class);
        $shellCommandLineExecutor
            ->expects($this->once())
            ->method('run')
            ->with(
                [
                    '/path/to/composer',
                    'require',
                    '--dev',
                    'infection/codeception-adapter',
                ],
                null,
                null,
                [],
                null,
                120.0,
            )
            ->willReturn(new CompletedProcess([], 0, '', ''));

        $installer = new AdapterInstaller(
            $composerExecutableFinder,
            $shellCommandLineExecutor,
        );

        $installer->install(TestFrameworkTypes::CODECEPTION);
    }
}
