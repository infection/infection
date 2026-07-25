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

namespace Infection\Tests\Architecture\PHPat\Selector\Support;

use PHPStan\Reflection\ClassReflection;
use function sprintf;
use function str_ends_with;
use function strlen;
use function substr;
use Symfony\Component\Filesystem\Path;

final readonly class EventArchitecture
{
    private const string SUBSCRIBER_SUFFIX = 'Subscriber';

    public function __construct(
        private string $projectRoot,
        private string $eventDirectory,
    ) {
    }

    public static function createDefault(): self
    {
        return new self(
            __DIR__ . '/../../../../../',
            'src/Event/Events',
        );
    }

    public function isInEventDirectory(ClassReflection $classReflection): bool
    {
        $fileName = $classReflection->getFileName();

        return $fileName !== null
            && Path::isBasePath(
                $this->eventDirectory,
                Path::makeRelative($fileName, $this->projectRoot),
            );
    }

    public function isEvent(ClassReflection $classReflection): bool
    {
        return $this->isInEventDirectory($classReflection)
            && ClassReflectionPredicates::isConcreteClass($classReflection)
            && !$this->isSubscriberName($classReflection->getName());
    }

    public function isSingleEventSubscriber(ClassReflection $classReflection): bool
    {
        return $this->isInEventDirectory($classReflection)
            && $classReflection->isInterface()
            && $this->isSubscriberName($classReflection->getName());
    }

    public function getSingleEventSubscriberName(ClassReflection $classReflection): string
    {
        return $classReflection->getName() . self::SUBSCRIBER_SUFFIX;
    }

    public function getSubscribedEventName(ClassReflection $classReflection): string
    {
        return substr(
            string: $classReflection->getName(),
            offset: 0,
            length: -strlen(self::SUBSCRIBER_SUFFIX),
        );
    }

    public function getExpectedSingleEventSubscriberMethodName(ClassReflection $classReflection): string
    {
        return sprintf(
            'on%s',
            substr(
                string: $classReflection->getNativeReflection()->getShortName(),
                offset: 0,
                length: -strlen(self::SUBSCRIBER_SUFFIX),
            ),
        );
    }

    private function isSubscriberName(string $className): bool
    {
        return str_ends_with($className, self::SUBSCRIBER_SUFFIX);
    }
}
