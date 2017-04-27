<?php

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\This;
use Infection\Tests\Mutator\AbstractMutator;

class ThisTest extends AbstractMutator
{
    public function test_does_not_mutate_returning_true()
    {
        $code = '<?php return true;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

return true;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }


    public function test_mutates_returning_this_variable()
    {
        $code = '<?php return $this;';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

return null;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator() : Mutator
    {
        return new This();
    }
}
