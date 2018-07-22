<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Utils;

use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class TmpDirectoryCreatorTest extends TestCase
{
    /**
     * @var TmpDirectoryCreator
     */
    private $creator;

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
        $this->fileSystem = new Filesystem();
        $this->creator = new TmpDirectoryCreator($this->fileSystem);
        $this->workspace = sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);
    }

    protected function tearDown(): void
    {
        $this->fileSystem->remove($this->workspace);
    }

    public function test_it_creates_and_return_path(): void
    {
        $this->assertDirectoryExists($this->creator->createAndGet($this->workspace));
    }
}
