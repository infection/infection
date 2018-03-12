<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Finder;

use Infection\Finder\Exception\FinderException;
use Infection\Finder\SourceFilesFinder;
use Infection\Finder\TestFrameworkFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class TestFrameworkFinderTest extends TestCase
{
    public function test_it_can_load_a_custom_path(): void
    {
        $filename = '/tmp/infection-test-framework-finder-test' . uniqid('', false);

        touch($filename);

        $frameworkFinder = new TestFrameworkFinder('not-used', $filename);

        static::assertEquals($filename, $frameworkFinder->find(), 'Should return the custom path');
    }

    public function test_invalid_custom_path_throws_exception(): void
    {
        $filename = '/tmp/infection-test-framework-finder-test' . uniqid('', false);

        $frameworkFinder = new TestFrameworkFinder('not-used', $filename);

        $this->expectException(FinderException::class);
        $this->expectExceptionMessageRegExp('/custom path/');

        $frameworkFinder->find();
    }
}
