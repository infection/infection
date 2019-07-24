<?php

declare(strict_types=1);

namespace Infection\Tests\AutoReview\ProjectCode;

use Generator;
use function Infection\Tests\generator_to_phpunit_data_provider;

trait ProjectCodeDataProvider
{
    public function sourceClassesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            ProjectCodeProvider::provideSourceClasses()
        );
    }

    public function concreteSourceClassesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            ProjectCodeProvider::provideConcreteSourceClasses()
        );
    }

    public function nonTestedConcreteClassesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            ProjectCodeProvider::NON_TESTED_CONCRETE_CLASSES
        );
    }

    public function sourceClassesToCheckForPublicPropertiesProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            ProjectCodeProvider::provideSourceClassesToCheckForPublicProperties()
        );
    }

    // "testClassesProvider" would be more correct but PHPUnit will then detect this method as a
    // test instead of a test provider.
    public function classesTestProvider(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            ProjectCodeProvider::provideTestClasses()
        );
    }

    public function nonFinalExtensionClasses(): Generator
    {
        yield from generator_to_phpunit_data_provider(
            ProjectCodeProvider::NON_FINAL_EXTENSION_CLASSES
        );
    }
}
