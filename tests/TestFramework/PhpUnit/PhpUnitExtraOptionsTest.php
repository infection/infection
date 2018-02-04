<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit;

use Infection\TestFramework\PhpUnit\PhpUnitExtraOptions;
use PHPUnit\Framework\TestCase;

class PhpUnitExtraOptionsTest extends TestCase
{
    /**
     * @dataProvider mutantProcessProvider
     */
    public function test_it_skips_filter_for_mutant_process(string $sourceExtraOptions, string $expectedExtraOptions)
    {
        $phpUnitOptions = new PhpUnitExtraOptions($sourceExtraOptions);

        $this->assertSame($expectedExtraOptions, $phpUnitOptions->getForMutantProcess());
    }

    public function test_it_returns_empty_string_when_source_options_are_null()
    {
        $phpUnitOptions = new PhpUnitExtraOptions(null);

        $this->assertSame('', $phpUnitOptions->getForInitialProcess());
        $this->assertSame('', $phpUnitOptions->getForMutantProcess());
    }

    public function mutantProcessProvider()
    {
        return [
            ['--filter=someTest#2 --a --b=value', '--a --b=value'],
            ['--a --filter=someTest#2 --b=value', '--a --b=value'],
            ['--a --filter someTest#2 --b=value', '--a --b=value'],
        ];
    }
}
