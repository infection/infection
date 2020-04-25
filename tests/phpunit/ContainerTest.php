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

use Infection\Container;
use Infection\FileSystem\Locator\FileNotFound;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
final class ContainerTest extends TestCase
{
    public function test_it_can_be_instantiated_without_any_services(): void
    {
        $container = new Container([]);

        try {
            $container->getFileSystem();

            $this->fail();
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                'Unknown service "Symfony\Component\Filesystem\Filesystem"',
                $exception->getMessage()
            );
        }
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
            Container::DEFAULT_CONFIG_FILE,
            Container::DEFAULT_MUTATORS_INPUT,
            Container::DEFAULT_SHOW_MUTATIONS,
            Container::DEFAULT_LOG_VERBOSITY,
            Container::DEFAULT_DEBUG,
            Container::DEFAULT_ONLY_COVERED,
            Container::DEFAULT_FORMATTER,
            Container::DEFAULT_NO_PROGRESS,
            Container::DEFAULT_FORCE_PROGRESS,
            '/path/to/coverage',
            Container::DEFAULT_INITIAL_TESTS_PHP_OPTIONS,
            Container::DEFAULT_SKIP_INITIAL_TESTS,
            Container::DEFAULT_IGNORE_MSI_WITH_NO_MUTATIONS,
            Container::DEFAULT_MIN_MSI,
            Container::DEFAULT_MIN_COVERED_MSI,
            Container::DEFAULT_MSI_PRECISION,
            Container::DEFAULT_TEST_FRAMEWORK,
            Container::DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS,
            Container::DEFAULT_FILTER,
            Container::DEFAULT_THREAD_COUNT,
            Container::DEFAULT_DRY_RUN
        );

        $traces = $newContainer->getFilteredEnrichedTraceProvider()->provideTraces();

        $this->assertIsIterable($traces);

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
            Container::DEFAULT_CONFIG_FILE,
            Container::DEFAULT_MUTATORS_INPUT,
            Container::DEFAULT_SHOW_MUTATIONS,
            Container::DEFAULT_LOG_VERBOSITY,
            Container::DEFAULT_DEBUG,
            Container::DEFAULT_ONLY_COVERED,
            Container::DEFAULT_FORMATTER,
            true,
            true,
            Container::DEFAULT_EXISTING_COVERAGE_PATH,
            Container::DEFAULT_INITIAL_TESTS_PHP_OPTIONS,
            Container::DEFAULT_SKIP_INITIAL_TESTS,
            Container::DEFAULT_IGNORE_MSI_WITH_NO_MUTATIONS,
            Container::DEFAULT_MIN_MSI,
            Container::DEFAULT_MIN_COVERED_MSI,
            Container::DEFAULT_MSI_PRECISION,
            Container::DEFAULT_TEST_FRAMEWORK,
            Container::DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS,
            Container::DEFAULT_FILTER,
            Container::DEFAULT_THREAD_COUNT,
            Container::DEFAULT_DRY_RUN
        );
    }
}
