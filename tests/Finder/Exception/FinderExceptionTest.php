<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Finder\Exception;

use Infection\Finder\Exception\FinderException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FinderExceptionTest extends TestCase
{
    public function test_composer_not_found_exception(): void
    {
        $exception = FinderException::composerNotFound();

        $this->assertInstanceOf(FinderException::class, $exception);
        $this->assertContains(
            'Unable to locate a Composer executable on local system',
            $exception->getMessage()
        );
    }

    public function test_php_executable_not_found(): void
    {
        $exception = FinderException::phpExecutableNotFound();

        $this->assertInstanceOf(FinderException::class, $exception);
        $this->assertContains(
            'Unable to locate the PHP executable on the local system',
            $exception->getMessage()
        );
    }

    public function test_test_framework_not_found(): void
    {
        $exception = FinderException::testFrameworkNotFound('framework');

        $this->assertInstanceOf(FinderException::class, $exception);
        $this->assertContains(
            'Unable to locate a framework executable on local system.',
            $exception->getMessage()
        );
        $this->assertContains(
            'Ensure that framework is installed and available.',
            $exception->getMessage()
        );
    }

    public function test_test_custom_path_does_not_exsist(): void
    {
        $exception = FinderException::testCustomPathDoesNotExist('framework', 'foo/bar/abc');

        $this->assertInstanceOf(FinderException::class, $exception);
        $this->assertContains(
            'The custom path to framework was set as "foo/bar/abc"',
            $exception->getMessage()
        );
    }
}
