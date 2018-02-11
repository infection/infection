<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\Finder;

use Infection\Finder\IgnoredMutatorsFinder;
use PHPUnit\Framework\TestCase;

class IgnoredMutatorsFinderTest extends TestCase
{
    public function test_it_returns_false_if_the_mutator_has_no_ignore()
    {
        $ignore = new IgnoredMutatorsFinder(['Plus' => 'foo/bar'], 'path');

        $this->assertFalse($ignore->isIgnored('GreaterThan', '', ''));
    }

    public function test_it_returns_true_if_star_is_an_option()
    {
        $ignore = new IgnoredMutatorsFinder(['Plus' => 'foo/bar', 'All' => '*'], 'path');

        $this->assertTrue($ignore->isIgnored('All', ''));
    }

    public function test_it_returns_true_if_file_is_ignored()
    {
        $ignore = new IgnoredMutatorsFinder(['Plus' => 'foo/bar/test.php', 'All' => ['*']], 'path');

        $this->assertTrue($ignore->isIgnored('Plus', 'path/foo/bar/test.php'));
    }

    public function test_it_returns_true_if_file_is_ignored_if_there_are_multiple()
    {
        $ignore = new IgnoredMutatorsFinder(['Plus' => ['foo/bar/test.php', 'other/path/test.php'], 'All' => ['*']], 'path');

        $this->assertTrue($ignore->isIgnored('Plus', 'path/other/path/test.php'));
    }

    public function test_it_returns_true_if_file_is_ignored_if_there_are_multiple_one_it_has_multiple_methods()
    {
        $ignore = new IgnoredMutatorsFinder(['Plus' => ['foo/bar/test.php', 'other/path/test.php' => ['a', 'b']], 'All' => ['*']], 'path');

        $this->assertTrue($ignore->isIgnored('Plus', 'path/other/path/test.php'));
    }
}
