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
use PHPat\Selector\ClassImplements;
use PHPat\Selector\Selector;
use PHPat\Selector\SelectorInterface;

final class InfectionSelector
{
    use CannotBeInstantiated;

    private static ?EventArchitecture $eventArchitecture = null;

    public static function code(): InfectionCode
    {
        return new InfectionCode();
    }

    public static function sourceCode(): InfectionSourceCode
    {
        return new InfectionSourceCode();
    }

    public static function testCode(): InfectionTestCode
    {
        return new InfectionTestCode();
    }

    public static function phpunitTestCode(): SelectorInterface
    {
        return Selector::AllOf(
            self::testCode(),
            Selector::withFilepath('#/tests/phpunit/#', true),
        );
    }

    public static function selectorFixtures(): SelectorInterface
    {
        return Selector::withFilepath('#/tests/phpunit/Architecture/PHPat/Selector/.+?/Fixture(s)?#', true);
    }

    public static function extensionPoint(): ExtensionPoint
    {
        return new ExtensionPoint();
    }

    public static function sourceConcreteClassWithoutCanonicalTest(): SourceConcreteClassWithoutCanonicalTest
    {
        return new SourceConcreteClassWithoutCanonicalTest();
    }

    public static function phpunitTestSupportConcreteClassWithoutCanonicalTest(): PHPUnitTestSupportConcreteClassWithoutCanonicalTest
    {
        return new PHPUnitTestSupportConcreteClassWithoutCanonicalTest();
    }

    public static function phpunitTestRequiringIoWithoutIntegrationGroup(): PHPUnitTestRequiringIoWithoutIntegrationGroup
    {
        return new PHPUnitTestRequiringIoWithoutIntegrationGroup(
            SingletonContainer::getContainer()->getFileSystem(),
        );
    }

    public static function phpunitTestNotRequiringIoWithIntegrationGroup(): PHPUnitTestNotRequiringIoWithIntegrationGroup
    {
        return new PHPUnitTestNotRequiringIoWithIntegrationGroup(
            SingletonContainer::getContainer()->getFileSystem(),
        );
    }

    public static function concretePHPUnitTestClass(): ConcretePHPUnitTestClass
    {
        return new ConcretePHPUnitTestClass();
    }

    public static function eventClassWithoutCorrespondingSingleEventSubscriber(): EventClassWithoutCorrespondingSingleEventSubscriber
    {
        return new EventClassWithoutCorrespondingSingleEventSubscriber(self::eventArchitecture());
    }

    public static function singleEventSubscriberWithoutCorrespondingEvent(): SingleEventSubscriberWithoutCorrespondingEvent
    {
        return new SingleEventSubscriberWithoutCorrespondingEvent(self::eventArchitecture());
    }

    public static function singleEventSubscriber(): SingleEventSubscriberSelector
    {
        return new SingleEventSubscriberSelector(self::eventArchitecture());
    }

    public static function singleEventSubscriberWithoutExpectedMethod(): SingleEventSubscriberWithoutExpectedMethod
    {
        return new SingleEventSubscriberWithoutExpectedMethod(self::eventArchitecture());
    }

    public static function eventDirectoryClassWithoutExpectedShape(): EventDirectoryClassWithoutExpectedShape
    {
        return new EventDirectoryClassWithoutExpectedShape(self::eventArchitecture());
    }

    public static function hasTrivialImplementation(): HasTrivialImplementation
    {
        $container = SingletonContainer::getContainer();

        return new HasTrivialImplementation(
            new Analyser(
                $container->getParser(),
                $container->getFileSystem(),
            ),
        );
    }

    public static function hasDocBlock(): HasDocBlock
    {
        return new HasDocBlock();
    }

    public static function hasInternalDocBlock(): HasInternalDocBlock
    {
        return new HasInternalDocBlock();
    }

    public static function isAnonymousClass(): IsAnonymousClass
    {
        return new IsAnonymousClass();
    }

    public static function implementsAnyInterface(): ClassImplements
    {
        return Selector::implements('/.*/', true);
    }

    private static function eventArchitecture(): EventArchitecture
    {
        return self::$eventArchitecture ??= EventArchitecture::createDefault();
    }
}
