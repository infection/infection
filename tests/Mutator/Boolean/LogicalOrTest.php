<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Test\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalOr;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class LogicalOrTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new LogicalOr();
    }

    public function test_replaces_logical_or_with_and()
    {
        $code = '<?php true || false;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

true && false;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
