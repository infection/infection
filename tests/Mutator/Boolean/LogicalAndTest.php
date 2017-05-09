<?php

namespace Humbug\Test\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalAnd;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class LogicalAndTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new LogicalAnd();
    }

    public function test_replaces_logical_and_with_or()
    {
        $code = '<?php true && false;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

true || false;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
