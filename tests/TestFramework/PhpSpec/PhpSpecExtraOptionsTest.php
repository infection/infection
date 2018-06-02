<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpSpec;

use Infection\TestFramework\PhpSpec\PhpSpecExtraOptions;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PhpSpecExtraOptionsTest extends TestCase
{
    /**
     * @dataProvider mutantProcessProvider
     */
    public function test_it_does_not_change_extra_options_mutant_process(string $sourceExtraOptions)
    {
        $phpUnitOptions = new PhpSpecExtraOptions($sourceExtraOptions);

        $this->assertSame($sourceExtraOptions, $phpUnitOptions->getForMutantProcess());
    }

    public function test_it_returns_empty_string_when_source_options_are_null()
    {
        $phpUnitOptions = new PhpSpecExtraOptions(null);

        $this->assertSame('', $phpUnitOptions->getForInitialProcess());
        $this->assertSame('', $phpUnitOptions->getForMutantProcess());
    }

    public function mutantProcessProvider()
    {
        return [
            ['--filter=someTest#2 --a --b=value'],
            ['--a --filter=someTest#2 --b=value'],
            ['--a --filter someTest#2 --b=value'],
        ];
    }
}
