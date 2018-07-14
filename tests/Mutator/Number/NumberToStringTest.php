<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Number;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class NumberToStringTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider providerNumber
     */
    public function test_it_casts_a_number_to_string($number)
    {
        $code = <<<PHP
<?php

echo ${number};
PHP;

        $mutatedCode = $this->mutate($code);

        $expectedMutatedCode = <<<PHP
<?php

echo '${number}';
PHP;

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    public function providerNumber(): array
    {
        return [
            [1.23],
            [123],
        ];
    }
}
