<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Utils;

use Infection\Filesystem\Filesystem;
use Infection\Utils\TmpDirectoryCreator;
use PHPUnit\Framework\TestCase;

class TmpDirectoryCreatorTest extends TestCase
{
    /**
     * @var TmpDirectoryCreator
     */
    private $creator;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    protected function setUp()
    {
        $this->fileSystem = new Filesystem();
        $this->creator = new TmpDirectoryCreator($this->fileSystem);
        $this->tmpDir = sprintf('%s/%s', sys_get_temp_dir(), TmpDirectoryCreator::BASE_DIR_NAME);
    }

    protected function tearDown()
    {
        $this->fileSystem->remove($this->tmpDir);
    }

    public function test_it_creates_and_return_path()
    {
        $this->creator->createAndGet($this->tmpDir);

        $this->assertDirectoryExists($this->tmpDir);
    }
}
