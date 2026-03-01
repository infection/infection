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

namespace Infection\Tests\Logger;

use function class_implements;
use function in_array;
use Infection\Event\Subscriber\EventSubscriber;
use Infection\Logger\DebugEventsSubscriber;
use function interface_exists;
use function is_a;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;
use function str_replace;
use function substr;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo as FinderSplFileInfo;

#[CoversClass(DebugEventsSubscriber::class)]
final class DebugEventsSubscriberTest extends TestCase
{
    private const SUBSCRIBERS_DIR = __DIR__ . '/../../../src/Event/Events';

    #[CoversNothing]
    #[DataProvider('subscriberProvider')]
    public function test_it_implements_all_subscribers(string $subscriberClassName): void
    {
        $this->assertImplementsSubscriber($subscriberClassName);
    }

    public static function subscriberProvider(): iterable
    {
        $finder = Finder::create()
            ->files()
            ->in(self::SUBSCRIBERS_DIR)
            ->name('*Subscriber.php');

        foreach ($finder as $fileInfo) {
            $subscriberClassName = self::getSubscriberClassName($fileInfo);

            if (self::isASubscriberInterface($subscriberClassName)) {
                yield [$subscriberClassName];
            }
        }
    }

    private function assertImplementsSubscriber(string $subscriberClassName): void
    {
        $this->assertTrue(
            is_a(
                DebugEventsSubscriber::class,
                $subscriberClassName,
                allow_string: true,
            ),
            sprintf(
                'Expected "%s" to be a "%s".',
                DebugEventsSubscriber::class,
                $subscriberClassName,
            ),
        );
    }

    /**
     * @return class-string
     */
    private static function getSubscriberClassName(FinderSplFileInfo $fileInfo): string
    {
        $canonicalRelativePathnameWithoutExtension = substr(
            Path::canonicalize($fileInfo->getRelativePathname()),
            offset: 0,
            length: -4,
        );

        return sprintf(
            'Infection\Event\Events\%s',
            str_replace(
                '/',
                '\\',
                $canonicalRelativePathnameWithoutExtension,
            ),
        );
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @phpstan-assert-if-true class-string<T>&class-string<EventSubscriber>
     */
    private static function isASubscriberInterface(string $className): bool
    {
        return interface_exists($className)
            && in_array(
                EventSubscriber::class,
                class_implements($className), true,
            );
    }
}
