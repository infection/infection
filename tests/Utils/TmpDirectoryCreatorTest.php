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
    private $workspace;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    protected function setUp()
    {
        $this->fileSystem = new Filesystem();
        $this->creator = new TmpDirectoryCreator($this->fileSystem);
        $this->workspace = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'infection-test' . \microtime(true) . \random_int(100, 999);
    }

    protected function tearDown()
    {
        $this->fileSystem->remove($this->workspace);
    }

    public function test_it_creates_and_return_path()
    {
        $this->assertDirectoryExists($this->creator->createAndGet($this->workspace));
    }
}
