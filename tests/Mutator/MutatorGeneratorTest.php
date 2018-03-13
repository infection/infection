<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator;

use Infection\Config\Exception\InvalidConfigException;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\MutatorGenerator;
use PHPUnit\Framework\TestCase;

class MutatorGeneratorTest extends TestCase
{
    public function test_no_setting_returns_the_default_mutators()
    {
        $mutatorGenerator = new MutatorGenerator([]);
        $mutators = $mutatorGenerator->create();

        $this->assertCount(55, $mutators);
    }

    public function test_boolean_mutator_returns_boolean_mutators()
    {
        $mutatorGenerator = new MutatorGenerator([
            '@boolean' => true,
        ]);
        $mutators = $mutatorGenerator->create();

        $this->assertCount(7, $mutators);
    }

    public function test_mutators_can_be_ignored()
    {
        $mutatorGenerator = new MutatorGenerator([
            '@default' => true,
            Plus::class => false,
        ]);
        $mutators = $mutatorGenerator->create();

        $this->assertCount(54, $mutators);
    }

    public function test_profiles_can_be_ignored()
    {
        $mutatorGenerator = new MutatorGenerator([
            '@default' => true,
            '@boolean' => false,
        ]);
        $mutators = $mutatorGenerator->create();

        $this->assertCount(48, $mutators);
    }

    public function test_names_can_be_ignored()
    {
        $mutatorGenerator = new MutatorGenerator([
            '@default' => true,
            Plus::getName() => false,
        ]);
        $mutators = $mutatorGenerator->create();

        $this->assertCount(54, $mutators);
    }

    public function test_it_throws_an_error_if_profile_does_not_exist()
    {
        $mutatorGenerator = new MutatorGenerator([
            '@bla-bla' => true,
        ]);

        $this->expectException(InvalidConfigException::class);
        $mutatorGenerator->create();
    }

    public function test_it_keeps_settings()
    {
        $mutatorGenerator = new MutatorGenerator([
            '@default' => true,
            Plus::getName() => ['ignore' => ['A::B']],
        ]);
        $mutators = $mutatorGenerator->create();

        $this->assertCount(55, $mutators);

        $this->assertInstanceOf(Plus::class, $mutators[Plus::getName()]);

        $this->assertTrue($mutators[Plus::getName()]->isIgnored('A', 'B'));
    }

    public function test_it_keeps_settings_when_applied_to_profiles()
    {
        $mutatorGenerator = new MutatorGenerator([
            '@default' => true,
            '@boolean' => ['ignore' => ['A::B']],
        ]);
        $mutators = $mutatorGenerator->create();

        $this->assertCount(55, $mutators);

        $this->assertInstanceOf(Plus::class, $mutators[Plus::getName()]);

        $this->assertTrue($mutators[TrueValue::getName()]->isIgnored('A', 'B'));
        $this->assertTrue($mutators[FalseValue::getName()]->isIgnored('A', 'B'));

        $this->assertFalse($mutators[Plus::getName()]->isIgnored('A', 'B'));
    }
}
