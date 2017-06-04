<?php

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
<fg=white>
<fg=red>--- Original</>
<fg=green>+++ New</>
@@ @@
     function ($a) {
<fg=red>-        return $a < 0;</>
<fg=green>+        return $a <= 0;</>
     }</>
CODE;

        $colorizer = new DiffColorizer();

        $this->assertSame($expectedColorizedDiff, $colorizer->colorize($diff));
    }
}