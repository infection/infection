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

namespace Infection\Tests;

use function class_exists;
use Error;
use function get_class;
use Infection\Container;
use Infection\FileSystem\Locator\FileNotFound;
use Infection\Testing\SingletonContainer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use function sprintf;
use function str_starts_with;
use Symfony\Component\Console\Output\NullOutput;
use Webmozart\Assert\InvalidArgumentException as AssertException;

#[Group('integration')]
#[CoversClass(Container::class)]
final class ContainerTest extends TestCase
{
    public function test_it_can_be_instantiated_without_any_services(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown service "Infection\FileSystem\SourceFileFilter"');

        $container = new Container([]);

        $container->getSourceFileFilter();
    }

    public function test_it_can_build_simple_services_without_configuration(): void
    {
        $container = new Container([]);

        $container->getFileSystem();

        $this->addToAssertionCount(1);
    }

    public function test_it_can_resolve_some_dependencies_without_configuration(): void
    {
        $container = new Container([]);

        $container->getAdapterInstallationDecider();

        $this->addToAssertionCount(1);
    }

    public function test_it_can_resolve_all_dependencies_with_configuration(): void
    {
        $container = Container::create();

        $container->getSubscriberRegisterer();
        $container->getTestFrameworkFinder();

        $this->addToAssertionCount(1);
    }

    public function test_it_can_be_instantiated_with_the_project_services(): void
    {
        $container = SingletonContainer::getContainer();

        $container->getFileSystem();

        $this->addToAssertionCount(1);
    }

    public function test_it_can_build_lazy_source_file_data_factory_that_fails_on_use(): void
    {
        $newContainer = SingletonContainer::getContainer()->withValues(
            new NullLogger(),
            new NullOutput(),
            existingCoveragePath: '/path/to/coverage',
        );

        $traces = $newContainer->getUnionTraceProvider()->provideTraces();

        $this->expectException(FileNotFound::class);
        $this->expectExceptionMessage('Could not find any "index.xml" file in "/path/to/coverage"');

        foreach ($traces as $trace) {
            $this->fail();
        }
    }

    public function test_it_provides_a_friendly_error_when_attempting_to_configure_it_with_both_no_progress_and_force_progress(): void
    {
        $container = SingletonContainer::getContainer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot force progress and set no progress at the same time');

        $container->withValues(
            new NullLogger(),
            new NullOutput(),
            noProgress: true,
            forceProgress: true,
        );
    }

    public static function provideServicesWithReflection(): iterable
    {
        foreach (self::getFactories(Container::create()) as $id => $factory) {
            yield $id => [$id];
        }
    }

    #[DataProvider('provideServicesWithReflection')]
    public function test_factory_is_essential(string $id): void
    {
        $container = Container::create();

        $this->unsetFactory($container, $id);

        try {
            $service = $this->createService($container, $id);
        } catch (InvalidArgumentException $e) {
            // All good: the service needs a factory
            $this->assertStringContainsString('Unknown service ', $e->getMessage());

            return;
        }

        // Another happy path: the service cannot be created without a factory
        if ($service === null) {
            $this->addToAssertionCount(1);

            return;
        }

        // All other services should be createable without a factory for this service
        foreach (self::iterateExpectedServices() as $expectedService) {
            try {
                $this->getService($container, $expectedService);
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('Unknown service ', $e->getMessage());

                // All good: this other service requires a factory for the original service
                return;
            }
        }

        echo "\n\$services[] = '$id';\n";

        $this->fail(sprintf(
            'Service "%s" may not require a factory (found "%s").',
            $id,
            $service ? get_class($service) : 'null',
        ));
    }

    public function test_it_can_provide_all_services(): void
    {
        $container = Container::create();

        foreach (self::iterateExpectedServices() as $methodName => $expectedService) {
            try {
                $service = $container->{$methodName}();
            } catch (Error|AssertException $e) {
                // Ignore services that require extra configuration
                continue;
            }

            $this->assertInstanceOf(
                $expectedService,
                $service,
                sprintf('Service should be an instance of "%s"', $expectedService),
            );
        }
    }

    private static function getFactories(Container $container): array
    {
        $reflection = new ReflectionClass($container);

        return $reflection->getProperty('factories')->getValue($container);
    }

    private static function unsetFactory(Container $container, string $id): void
    {
        $reflection = new ReflectionClass($container);

        foreach (['factories', 'values'] as $propName) {
            $property = $reflection->getProperty($propName);
            $value = $property->getValue($container);

            unset($value[$id]);
            $property->setValue($container, $value);
        }
    }

    private static function createService(Container $container, string $id): ?object
    {
        $reflection = new ReflectionClass($container);

        try {
            return $reflection->getMethod('createService')->invoke($container, $id);
        } catch (Error|AssertException $e) {
            // Ignore services that require extra configuration
            return null;
        }
    }

    private static function getService(Container $container, string $id): ?object
    {
        $reflection = new ReflectionClass($container);

        try {
            return $reflection->getMethod('get')->invoke($container, $id);
        } catch (Error|AssertException $e) {
            // Ignore services that require extra configuration
            return null;
        }
    }

    private static function iterateExpectedServices(): iterable
    {
        $reflection = new ReflectionClass(Container::class);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (!str_starts_with($method->getName(), 'get')) {
                continue;
            }

            $returnType = $method->getReturnType();

            if (!$returnType instanceof ReflectionNamedType || $returnType->isBuiltin()) {
                continue;
            }

            $typeReflection = new ReflectionClass($returnType->getName());

            if ($typeReflection->isInterface()) {
                continue;
            }

            yield $method->getName() => $returnType->getName();
        }
    }
}
