<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Utils;

use Infection\Filesystem\Filesystem;
use Infection\Utils\TempDirectoryCreator;
use PHPUnit\Framework\TestCase;

class TempDirectoryCreatorTest extends TestCase
{
    /**
     * @var TempDirectoryCreator
     */
    private $creator;

    /**
     * @var string
     */
    private $tempDir;

    protected function setUp()
    {
        $this->creator = new TempDirectoryCreator(new Filesystem());
        $this->tempDir = sys_get_temp_dir();
    }

    protected function tearDown()
    {
        @unlink($this->tempDir);
    }

    public function test_it_creates_and_return_path()
    {
        $this->creator->createAndGet($this->tempDir);

        $fullPath = sprintf('%s/%s', $this->tempDir, TempDirectoryCreator::BASE_DIR_NAME);

        $this->assertTrue(is_dir($fullPath));
    }
}
