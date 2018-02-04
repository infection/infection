<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Differ;

use Infection\Differ\DiffColorizer;
use PHPUnit\Framework\TestCase;

class DiffColorizerTest extends TestCase
{
    public function test_id_adds_colours()
    {
        $diff = <<<'CODE'
--- Original
+++ New
@@ @@
     function ($a) {
-        return $a < 0;
+        return $a <= 0;
     }
CODE;

        $expectedColorizedDiff = <<<'CODE'
<code>
<diff-del>--- Original</diff-del>
<diff-add>+++ New</diff-add>
@@ @@
     function ($a) {
<diff-del>-        return $a < 0;</diff-del>
<diff-add>+        return $a <= 0;</diff-add>
     }</code>
CODE;

        $colorizer = new DiffColorizer();

        $this->assertSame($expectedColorizedDiff, $colorizer->colorize($diff));
    }
}
