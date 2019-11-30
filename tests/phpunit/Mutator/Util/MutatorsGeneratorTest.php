<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Util;

use function count;
use Infection\Config\Exception\InvalidConfigException;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\ProfileList;
use Infection\Mutator\Util\MutatorsGenerator;
use Infection\Tests\Fixtures\StubMutator;
use Infection\Visitor\ReflectionVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Plus as PlusNode;
use PhpParser\Node\Scalar\DNumber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class MutatorsGeneratorTest extends TestCase
{
    private static $countDefaultMutators = 0;

    public static function setUpBeforeClass(): void
    {
        foreach (ProfileList::DEFAULT_PROFILE as $profileName) {
            self::$countDefaultMutators += count(\Infection\Mutator\ProfileList::ALL_PROFILES[$profileName]);
        }
    }

    public function test_no_setting_returns_the_default_mutators(): void
    {
        $mutators = (new MutatorsGenerator([]))->generate();

        $this->assertCount(self::$countDefaultMutators, $mutators);
    }

    public function test_boolean_mutator_returns_boolean_mutators(): void
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@boolean' => true,
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(count(ProfileList::BOOLEAN_PROFILE), $mutators);
    }

    public function test_mutators_can_be_ignored(): void
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@default' => true,
            Plus::class => false,
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(self::$countDefaultMutators - 1, $mutators);
    }

    public function test_profiles_can_be_ignored(): void
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@default' => true,
            '@boolean' => false,
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(self::$countDefaultMutators - count(\Infection\Mutator\ProfileList::BOOLEAN_PROFILE), $mutators);
    }

    public function test_names_can_be_ignored(): void
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@default' => true,
            Plus::getName() => false,
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(self::$countDefaultMutators - 1, $mutators);
    }

    public function test_it_throws_an_error_if_profile_does_not_exist(): void
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@bla-bla' => true,
        ]);

        $this->expectException(InvalidConfigException::class);
        $mutatorGenerator->generate();
    }

    public function test_it_keeps_settings(): void
    {
        /** @var MockObject|ReflectionClass $reflectionMock */
        $reflectionMock = $this->createMock(ReflectionClass::class);
        $reflectionMock->expects($this->once())
            ->method('getName')
            ->willReturn('A');

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

    public function test_it_keeps_settings_when_applied_to_profiles(): void
    {
        /** @var MockObject|ReflectionClass $reflectionMock */
        $reflectionMock = $this->createMock(ReflectionClass::class);
        $reflectionMock->expects($this->exactly(3))
            ->method('getName')
            ->willReturn('A');

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

    public function test_it_accepts_custom_mutators(): void
    {
        $mutatorGenerator = new MutatorsGenerator([
            'Infection\\Tests\\Fixtures\\StubMutator' => true,
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertArrayHasKey('StubMutator', $mutators);
        $this->assertInstanceOf(StubMutator::class, $mutators['StubMutator']);
    }

    public function test_it_combines_custom_mutators_with_the_other_mutators(): void
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@default' => true,
            'Infection\\Tests\\Fixtures\\StubMutator' => true,
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertNotCount(1, $mutators);
        $this->assertCount(self::$countDefaultMutators + 1, $mutators);
    }

    public function test_it_can_set_a_single_item_with_a_setting(): void
    {
        /** @var MockObject|ReflectionClass $reflectionMock */
        $reflectionMock = $this->createMock(ReflectionClass::class);
        $reflectionMock->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('A');

        $falseNode = $this->getBoolNode('false', 'B', $reflectionMock);
        $trueNode = $this->getBoolNode('true', 'B', $reflectionMock);

        $mutatorGenerator = new MutatorsGenerator([
            '@boolean' => ['ignore' => ['A::B']],
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(count(ProfileList::BOOLEAN_PROFILE), $mutators);

        $this->assertArrayNotHasKey(Plus::getName(), $mutators);

        $this->assertFalse($mutators[TrueValue::getName()]->shouldMutate($trueNode));
        $this->assertFalse($mutators[FalseValue::getName()]->shouldMutate($falseNode));
    }

    public function test_an_empty_setting_is_allowed(): void
    {
        $mutatorGenerator = new MutatorsGenerator([
            '@boolean' => [],
        ]);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(count(ProfileList::BOOLEAN_PROFILE), $mutators);

        $this->assertArrayNotHasKey(Plus::getName(), $mutators);
    }

    /**
     * This is default behaviour since we json decode our config file as an stdclass
     */
    public function test_it_works_when_supplied_with_an_std_class_as_setting(): void
    {
        $mutatorSettings = json_decode(<<<'JSON'
{
    "@default": true,
    "Infection\\Mutator\\Boolean\\Yield_": false,
    "Plus": false,
    "Minus": {
        "ignore": ["A::B"]
    }

}
JSON
        );

        $mutatorGenerator = new MutatorsGenerator((array) $mutatorSettings);
        $mutators = $mutatorGenerator->generate();

        $this->assertCount(self::$countDefaultMutators - 2, $mutators);
    }

    private function getPlusNode(string $functionName, ReflectionClass $reflectionMock): Node
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

    private function getBoolNode(string $boolean, string $functionName, ReflectionClass $reflectionMock): Node
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
