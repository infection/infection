<?php

declare(strict_types=1);

namespace Infection\Tests\Mutator\ReturnValue;

use Infection\Mutator\Mutator;
use Infection\Mutator\ReturnValue\FloatNegation;
use Infection\Mutator\ReturnValue\IntegerNegation;
use Infection\Mutator\ReturnValue\NewObject;
use Infection\Tests\Mutator\AbstractMutator;


class NewObjectTest extends AbstractMutator
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


    public function test_mutates_instantiation_of_new_object_with_return()
    {
        $code = '<?php return new \DateTime();';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

new \DateTime();
return null;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function test_mutates_instantiation_of_new_object_with_params()
    {
        $code = '<?php return new \DateTime("now");';
        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<'CODE'
<?php

new \DateTime("now");
return null;
CODE;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator() : Mutator
    {
        return new NewObject();
    }
}
