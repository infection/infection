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

namespace Infection\Tests\Container;

use function array_keys;
use Error;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Container\Container;
use Infection\TestFramework\Coverage\Locator\Throwable\ReportLocationThrowable;
use Infection\Testing\SingletonContainer;
use Infection\Tests\Reflection\ContainerReflection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use function sprintf;
use Symfony\Component\Console\Output\NullOutput;
use Webmozart\Assert\InvalidArgumentException as AssertException;

#[CoversNothing]
#[Group('integration')]
final class ContainerTest extends TestCase
{
    public function test_it_can_be_instantiated_without_any_services(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown service "Infection\Configuration\SourceFilter\PlainFilter"');

        $container = new Container([]);

        $container->get(PlainFilter::class);
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
        $container = SingletonContainer::getContainer();

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

        $traces = $newContainer->getTraceProvider()->provideTraces();

        $this->expectException(ReportLocationThrowable::class);

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
        $reflection = new ContainerReflection(
            SingletonContainer::getContainer(),
        );

        foreach (array_keys($reflection->getFactories()) as $id) {
            yield $id => [$id];
        }
    }

    /**
     * @param class-string $id
     */
    #[DataProvider('provideServicesWithReflection')]
    public function test_factory_is_essential(string $id): void
    {
        $reflection = new ContainerReflection(Container::create());

        $reflection->unsetFactory($id);

        try {
            $service = $reflection->createService($id);
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

        $this->assertInstanceOf(
            $id,
            $service,
            sprintf('Service should be an instance of "%s"', $id),
        );

        // Here we can check that all other services can be created without a factory for this service
        // Iterate over $reflection->iterateExpectedConcreteServices(), calling getService() for each service
    }

    public static function provideExpectedConcreteServicesWithReflection(): iterable
    {
        $container = Container::create();
        $reflection = new ContainerReflection($container);

        foreach ($reflection->iterateExpectedConcreteServices() as $methodName => $id) {
            yield $methodName => [$id, $methodName, $container, $reflection];
        }
    }

    /**
     * @param class-string $id
     */
    #[DataProvider('provideExpectedConcreteServicesWithReflection')]
    public function test_it_can_provide_all_services(string $id, string $methodName, Container $container, ContainerReflection $reflection): void
    {
        try {
            $service = $container->{$methodName}();
        } catch (Error|AssertException) {
            // Ignore services that require extra configuration
            $this->addToAssertionCount(1);

            return;
        }

        $this->assertInstanceOf(
            $id,
            $service,
            sprintf('Service should be an instance of "%s"', $id),
        );

        $this->assertSame($service, $reflection->getService($id));
    }
}
