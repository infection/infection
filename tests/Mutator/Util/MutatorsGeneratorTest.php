<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Util;

use Infection\Config\Exception\InvalidConfigException;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Util\MutatorProfile;
use Infection\Mutator\Util\MutatorsGenerator;
use Infection\Visitor\ReflectionVisitor;
use Mockery;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Plus as PlusNode;
use PhpParser\Node\Scalar\DNumber;

class MutatorsGeneratorTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    private static $countDefaultMutators = 0;

    public static function setUpBeforeClass()
    {
        foreach (MutatorProfile::DEFAULT as $profileName) {
            self::$countDefaultMutators += count(MutatorProfile::MUTATOR_PROFILE_LIST[$profileName]);
        }
    }

    public function test_no_setting_returns_the_default_mutators()
    {
        $mutatorGenerator = new MutatorsGenerator([]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(self::$countDefaultMutators, $mutators);
    }

    public function test_boolean_mutator_returns_boolean_mutators()
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@boolean' => true,
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(count(MutatorProfile::BOOLEAN), $mutators);
    }

    public function test_mutators_can_be_ignored()
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@default' => true,
            Plus::class => false,
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(self::$countDefaultMutators - 1, $mutators);
    }

    public function test_profiles_can_be_ignored()
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@default' => true,
            '@boolean' => false,
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(self::$countDefaultMutators - count(MutatorProfile::BOOLEAN), $mutators);
    }

    public function test_names_can_be_ignored()
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@default' => true,
            Plus::getName() => false,
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(self::$countDefaultMutators - 1, $mutators);
    }

    public function test_it_throws_an_error_if_profile_does_not_exist()
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@bla-bla' => true,
        ]);

        $this->expectException(InvalidConfigException::class);
        $mutatorGenerator->generate();
    }

    public function test_it_keeps_settings()
    {
        $reflectionMock = Mockery::mock(\ReflectionClass::class);
        $reflectionMock->shouldReceive('getName')->once()->andReturn('A');
        $plusNode = $this->getPlusNode('B', $reflectionMock);

        $mutatorGenerator = new MutatorsGenerator([
            '@default' => true,
            Plus::getName() => ['ignore' => ['A::B']],
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(self::$countDefaultMutators, $mutators);

        $this->assertInstanceOf(Plus::class, $mutators[Plus::getName()]);

        $this->assertFalse($mutators[Plus::getName()]->shouldMutate($plusNode));
    }

    public function test_it_keeps_settings_when_applied_to_profiles()
    {
        $reflectionMock = Mockery::mock(\ReflectionClass::class);
        $reflectionMock->shouldReceive('getName')->times(3)->andReturn('A');
        $plusNode = $this->getPlusNode('B', $reflectionMock);
        $falseNode = $this->getBoolNode('false', 'B', $reflectionMock);
        $trueNode = $this->getBoolNode('true', 'B', $reflectionMock);

        $mutatorGenerator = new MutatorsGenerator([
            '@default' => true,
            '@boolean' => ['ignore' => ['A::B']],
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(self::$countDefaultMutators, $mutators);

        $this->assertInstanceOf(Plus::class, $mutators[Plus::getName()]);

        $this->assertFalse($mutators[TrueValue::getName()]->shouldMutate($trueNode));
        $this->assertFalse($mutators[FalseValue::getName()]->shouldMutate($falseNode));

        $this->assertTrue($mutators[Plus::getName()]->shouldMutate($plusNode));
    }

    private function getPlusNode(string $functionName, \ReflectionClass $reflectionMock): Node
    {
        return new PlusNode(
            new DNumber(1.23),
            new DNumber(1.23),
            [
                ReflectionVisitor::REFLECTION_CLASS_KEY => $reflectionMock,
                ReflectionVisitor::FUNCTION_NAME => $functionName,
            ]
        );
    }

    private function getBoolNode(string $boolean, string $functionName, \ReflectionClass $reflectionMock): Node
    {
        return new Node\Expr\ConstFetch(
            new Node\Name($boolean),
            [
                ReflectionVisitor::REFLECTION_CLASS_KEY => $reflectionMock,
                ReflectionVisitor::FUNCTION_NAME => $functionName,
            ]
        );
    }
}
