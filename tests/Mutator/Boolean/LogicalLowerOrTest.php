<?php

namespace Humbug\Test\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalAnd;
use Infection\Mutator\Boolean\LogicalLowerAnd;
use Infection\Mutator\Boolean\LogicalLowerOr;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;

class LogicalLowerOrTest extends AbstractMutator
{
    protected function getMutator(): Mutator
    {
        return new LogicalLowerOr();
    }

    public function test_replaces_logical_lower_or_with_and()
    {
        $code = '<?php true or false;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

true and false;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }
}
