<?php

declare(strict_types=1);

namespace Infection\Tests\Mutation;

use Infection\Mutation\MutationCalculatedState;
use PHPUnit\Framework\TestCase;

final class MutationCalculatedStateTest extends TestCase
{
    use MutationCalculatedStateAssertions;

    public function test_it_can_be_instantiated(): void
    {
        $state = new MutationCalculatedState(
            $hash = '0800f',
            $filePath = '/path/to/mutation',
            $code = 'notCovered#0',
            $diff = <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

DIFF
        );

        $this->assertSame($hash, $state->getMutationHash());
        $this->assertSame($filePath, $state->getMutationFilePath());
        $this->assertSame($code, $state->getMutatedCode());
        $this->assertSame($diff, $state->getDiff());
    }
}
