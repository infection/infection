<?php

declare(strict_types=1);

namespace Infection\Tests\AutoReview\ProjectCode;

use PHPUnit\Framework\TestCase;
use function Safe\sprintf;
use function trait_exists;

/**
 * @requires ProjectCodeProviderTest
 */
final class ProjectCodeProviderTest extends TestCase
{
    use ProjectCodeDataProvider;

    /**
     * @dataProvider sourceClassesProvider
     */
    public function test_source_class_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true)
            || interface_exists($className, true)
            || trait_exists($className, true),
            sprintf(
                'The "%s" class was picked up by the source files finder, but it is not a '
                .'class, interface or trait. Please check for typos in the class name. If the '
                .' problematic file is not a class file declaration, add it to the list of '
                .'excluded files in %s::provideSourceClasses().',
                $className,
                ProjectCodeProvider::class
            )
        );
    }

    /**
     * @dataProvider concreteSourceClassesProvider
     */
    public function test_concrete_class_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true),
            sprintf(
                'Expected "%s" to be a class.',
                $className
            )
        );
    }

    /**
     * @dataProvider nonTestedConcreteClassesProvider
     */
    public function test_non_tested_concrete_class_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true),
            sprintf(
                'The class "%s" no longer exists. Please remove it from the list of non tested '
                .'classes in %s::NON_TESTED_CONCRETE_CLASSES.',
                $className,
                ProjectCodeProvider::class
            )
        );
    }

    /**
     * @dataProvider sourceClassesToCheckForPublicPropertiesProvider
     */
    public function test_source_classes_to_check_for_public_properties_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true) || trait_exists($className, true),
            sprintf(
                'Expected "%s" to be either a class or a trait.',
                $className
            )
        );
    }

    /**
     * @dataProvider classesTestProvider
     */
    public function test_test_classes_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true)
            || interface_exists($className, true)
            || trait_exists($className, true),
            sprintf(
                'The "%s" class was picked up by the test files finder, but it not a class,'
                .' interface or trait. Please check for typos in the class name. If the '
                .' problematic file is not a class file declaration, add it to the list of '
                .'excluded files in %s::provideTestClasses().',
                $className,
                ProjectCodeProvider::class
            )
        );
    }

    /**
     * @dataProvider nonFinalExtensionClasses
     */
    public function test_non_final_extension_classes_provider_is_valid(string $className): void
    {
        $this->assertTrue(
            class_exists($className, true)
            || interface_exists($className, true)
            || trait_exists($className, true),
            sprintf(
                'The "%s" class was picked up by the test files finder, but it not a class,'
                .' interface or trait. Please check for typos in the class name. If the '
                .' class no longer exists, remove it from %s::NON_FINAL_EXTENSION_CLASSES.',
                $className,
                ProjectCodeProvider::class
            )
        );
    }
}
