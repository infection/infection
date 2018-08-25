<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Util;

use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Util\MutatorParser;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MutatorParserTest extends TestCase
{
    public function test_it_returns_default_mutators_when_no_input_mutators(): void
    {
        $parser = new MutatorParser(null, [1, 2, 3]);

        $this->assertSame([1, 2, 3], $parser->getMutators());
    }

    public function test_it_throws_an_exception_when_mutators_is_only_whitespace(): void
    {
        $parser = new MutatorParser('    ', [1, 2, 3]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "--mutators" option requires a value.');

        $parser->getMutators();
    }

    public function test_it_generates_a_single_mutator_from_the_input_string(): void
    {
        $parser = new MutatorParser('TrueValue', []);

        $mutatorList = $parser->getMutators();

        $this->assertCount(1, $mutatorList);
        $this->assertInstanceOf(TrueValue::class, array_shift($mutatorList));
    }

    public function test_it_generates_multiple_mutators_from_the_input_string(): void
    {
        $parser = new MutatorParser('TrueValue,FalseValue', []);

        $mutatorList = $parser->getMutators();

        $this->assertCount(2, $mutatorList);
        $this->assertInstanceOf(TrueValue::class, array_shift($mutatorList));
        $this->assertInstanceOf(FalseValue::class, array_shift($mutatorList));
    }
}
