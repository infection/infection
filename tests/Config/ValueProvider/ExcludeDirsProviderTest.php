<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Config\ValueProvider\ExcludeDirsProvider;
use Mockery;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class ExcludeDirsProviderTest extends AbstractBaseProviderTest
{
    /**
     * @var string
     */
    private $workspace;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    protected function setUp(): void
    {
        $this->workspace = \sys_get_temp_dir() . '/exclude' . \microtime(true) . \random_int(100, 999);
        \mkdir($this->workspace, 0777, true);

        $this->fileSystem = new Filesystem();
    }

    protected function tearDown(): void
    {
        $this->fileSystem->remove($this->workspace);
    }

    /**
     * @dataProvider excludeDirsProvider
     */
    public function test_it_contains_vendors_when_sources_contains_current_dir(string $excludedRootDir, array $dirsInCurrentFolder): void
    {
        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $provider = new ExcludeDirsProvider($consoleMock, $dialog, $this->fileSystem);

        $excludedDirs = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("\n")),
            $this->createOutputInterface(),
            $dirsInCurrentFolder,
            ['.']
        );

        $this->assertContains($excludedRootDir, $excludedDirs);
    }

    public function test_it_validates_dirs(): void
    {
        if (!$this->hasSttyAvailable()) {
            $this->markTestSkipped('Stty is not available');
        }

        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $provider = new ExcludeDirsProvider($consoleMock, $dialog, $this->fileSystem);

        $excludeDirs = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("abc\n")),
            $this->createOutputInterface(),
            ['src'],
            ['src']
        );

        $this->assertCount(0, $excludeDirs);
    }

    public function test_passes_when_correct_dir_typed(): void
    {
        if (!$this->hasSttyAvailable()) {
            $this->markTestSkipped('Stty is not available');
        }

        $dir1 = $this->workspace . \DIRECTORY_SEPARATOR . 'test' . \DIRECTORY_SEPARATOR;
        $dir2 = $this->workspace . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR;

        \mkdir($dir1);
        \mkdir($dir2);

        $consoleMock = Mockery::mock(ConsoleHelper::class);
        $consoleMock->shouldReceive('getQuestion')->once()->andReturn('?');

        $dialog = $this->getQuestionHelper();

        $provider = new ExcludeDirsProvider($consoleMock, $dialog, $this->fileSystem);

        $excludeDirs = $provider->get(
            $this->createStreamableInputInterfaceMock($this->getInputStream("foo\n")),
            $this->createOutputInterface(),
            ['src'],
            [$this->workspace]
        );

        $this->assertContains('foo', $excludeDirs);
    }

    public function excludeDirsProvider()
    {
        return array_map(
            function (string $excludedRootDir) {
                return [$excludedRootDir, [$excludedRootDir, 'src']];
            },
            ExcludeDirsProvider::EXCLUDED_ROOT_DIRS
        );
    }
}
