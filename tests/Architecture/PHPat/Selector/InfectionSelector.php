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

namespace Infection\Tests\Architecture\PHPat\Selector;

use Infection\CannotBeInstantiated;
use Infection\Testing\SingletonContainer;
use Infection\Tests\Architecture\PHPat\Selector\Support\Analyser\Analyser;
use Infection\Tests\Architecture\PHPat\Selector\Support\EventArchitecture;
use Infection\Tests\Architecture\PHPat\Selector\Support\IoCodeDetector;
use PHPat\Selector\Selector;
use PHPat\Selector\SelectorInterface;
use PHPStan\Reflection\ReflectionProvider;
use Webmozart\Assert\Assert;

final class InfectionSelector
{
    use CannotBeInstantiated;

    private static ?EventArchitecture $eventArchitecture = null;

    private static ?Analyser $analyser = null;

    private static ?IoCodeDetector $ioCodeDetector = null;

    private static ?ReflectionProvider $ioCodeDetectorReflectionProvider = null;

    public static function code(): SelectorInterface
    {
        return new InfectionCode();
    }

    public static function sourceCode(): SelectorInterface
    {
        return new InfectionSourceCode();
    }

    public static function nonSourceCode(): SelectorInterface
    {
        return Selector::Not(self::sourceCode());
    }

    public static function testCode(): SelectorInterface
    {
        return new InfectionTestCode();
    }

    public static function magoAdapterCandidate(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::inNamespace('Infection\StaticAnalysis\Mago'),
            Selector::inNamespace('Infection\TestFramework\Mago'),
        );
    }

    public static function phpStanAdapterCandidate(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::inNamespace('Infection\StaticAnalysis\PHPStan'),
            Selector::inNamespace('Infection\TestFramework\PhpStan'),
        );
    }

    public static function testFrameworkContractCandidate(): SelectorInterface
    {
        return Selector::inNamespace('Infection\TestFramework\Contracts');
    }

    public static function phpUnitAdapterCandidate(): SelectorInterface
    {
        return Selector::inNamespace('Infection\TestFramework\PhpUnit');
    }

    public static function adapterCommonCandidate(): SelectorInterface
    {
        return Selector::AnyOf(
            Selector::inNamespace('Infection\TestFramework\Common'),
            new ClassNamedAny([
                // This class can simply be copied when we need it.
                CannotBeInstantiated::class,
            ]),
        );
    }

    public static function phpunitTestCode(): SelectorInterface
    {
        return Selector::AllOf(
            self::testCode(),
            Selector::withFilepath('#/tests/phpunit/#', true),
        );
    }

    public static function phpUnitTestsWithCoversNothing(): SelectorInterface
    {
        return new PHPUnitTestWithCoversNothing(self::analyser());
    }

    public static function integrationPhpUnitTests(): SelectorInterface
    {
        return new IntegrationPHPUnitTest(self::analyser());
    }

    public static function selectorFixtures(): SelectorInterface
    {
        return Selector::withFilepath('#/tests/phpunit/Architecture/PHPat/Selector/.+?/Fixture(s)?#', true);
    }

    public static function extensionPoint(): SelectorInterface
    {
        return new ExtensionPoint();
    }

    public static function sourceConcreteClassWithoutCanonicalTest(): SelectorInterface
    {
        return new SourceConcreteClassWithoutCanonicalTest();
    }

    public static function phpunitTestSupportConcreteClassWithoutCanonicalTest(): SelectorInterface
    {
        return new PHPUnitTestSupportConcreteClassWithoutCanonicalTest();
    }

    public static function phpunitTestRequiringIoWithoutIntegrationGroup(ReflectionProvider $reflectionProvider): SelectorInterface
    {
        return Selector::AllOf(
            new PHPUnitTestRequiringIoWithoutIntegrationGroup(
                self::getIoCodeDetector($reflectionProvider),
                self::analyser(),
            ),
            Selector::Not(self::autoreviewTestCode()),
        );
    }

    public static function phpunitTestNotRequiringIoWithIntegrationGroup(ReflectionProvider $reflectionProvider): SelectorInterface
    {
        return new PHPUnitTestNotRequiringIoWithIntegrationGroup(
            self::getIoCodeDetector($reflectionProvider),
        );
    }

    public static function autoreviewTestCode(): SelectorInterface
    {
        return Selector::AllOf(
            Selector::AnyOf(
                Selector::inNamespace('Infection\Tests\Architecture'),
                Selector::inNamespace('Infection\Tests\AutoReview'),
            ),
            Selector::Not(self::selectorFixtures()),
        );
    }

    public static function concretePHPUnitTestClass(): SelectorInterface
    {
        return new ConcretePHPUnitTestClass();
    }

    public static function eventClassWithoutCorrespondingSingleEventSubscriber(): SelectorInterface
    {
        return new EventClassWithoutCorrespondingSingleEventSubscriber(self::eventArchitecture());
    }

    public static function singleEventSubscriberWithoutCorrespondingEvent(): SelectorInterface
    {
        return new SingleEventSubscriberWithoutCorrespondingEvent(self::eventArchitecture());
    }

    public static function singleEventSubscriber(): SelectorInterface
    {
        return new SingleEventSubscriberSelector(self::eventArchitecture());
    }

    public static function singleEventSubscriberWithoutExpectedMethod(): SelectorInterface
    {
        return new SingleEventSubscriberWithoutExpectedMethod(self::eventArchitecture());
    }

    public static function eventDirectoryClassWithoutExpectedShape(): SelectorInterface
    {
        return new EventDirectoryClassWithoutExpectedShape(self::eventArchitecture());
    }

    public static function hasTrivialImplementation(): SelectorInterface
    {
        return new HasTrivialImplementation(self::analyser());
    }

    public static function sourceClassWithPublicNonReadonlyProperty(): SelectorInterface
    {
        return new SourceClassWithPublicNonReadonlyProperty(self::analyser());
    }

    public static function staticOrConstOnlyClass(): SelectorInterface
    {
        return new StaticOrConstOnlyClass();
    }

    public static function classWithNoArgumentPrivateConstructor(): SelectorInterface
    {
        return new ClassWithNoArgumentPrivateConstructor();
    }

    public static function hasDocBlock(): SelectorInterface
    {
        return new HasDocBlock();
    }

    public static function hasInternalDocBlock(): SelectorInterface
    {
        return new HasInternalDocBlock();
    }

    public static function isAnonymousClass(): SelectorInterface
    {
        return new IsAnonymousClass();
    }

    public static function implementsAnyInterface(): SelectorInterface
    {
        return Selector::implements('/.*/', true);
    }

    private static function eventArchitecture(): EventArchitecture
    {
        return self::$eventArchitecture ??= EventArchitecture::createDefault();
    }

    private static function analyser(): Analyser
    {
        if (self::$analyser === null) {
            $container = SingletonContainer::getContainer();

            self::$analyser = new Analyser(
                $container->getParser(),
                $container->getFileSystem(),
            );
        }

        return self::$analyser;
    }

    private static function getIoCodeDetector(ReflectionProvider $reflectionProvider): IoCodeDetector
    {
        if (self::$ioCodeDetector === null) {
            self::$ioCodeDetectorReflectionProvider = $reflectionProvider;

            return self::$ioCodeDetector = new IoCodeDetector(
                self::analyser(),
                $reflectionProvider,
            );
        }

        Assert::same(
            self::$ioCodeDetectorReflectionProvider,
            $reflectionProvider,
            'I/O code detector must be requested with the same reflection provider.',
        );

        return self::$ioCodeDetector;
    }
}
