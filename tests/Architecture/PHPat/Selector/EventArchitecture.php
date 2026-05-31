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
use PHPStan\Reflection\ClassReflection;
use Symfony\Component\Filesystem\Path;
use function str_ends_with;
use function strlen;
use function substr;

final class EventArchitecture
{
    use CannotBeInstantiated;

    private const string PROJECT_ROOT = __DIR__ . '/../../../../';

    private const string EVENT_DIRECTORY = 'src/Event/Events';

    private const string SUBSCRIBER_SUFFIX = 'Subscriber';

    public static function isInEventDirectory(ClassReflection $classReflection): bool
    {
        $fileName = $classReflection->getFileName();

        return $fileName !== null
            && Path::isBasePath(
                self::EVENT_DIRECTORY,
                Path::makeRelative($fileName, self::PROJECT_ROOT),
            );
    }

    public static function isEvent(ClassReflection $classReflection): bool
    {
        $nativeReflection = $classReflection->getNativeReflection();

        return self::isInEventDirectory($classReflection)
            && !$nativeReflection->isInterface()
            && !$nativeReflection->isTrait()
            && !$nativeReflection->isEnum()
            && !self::isSubscriberName($classReflection->getName());
    }

    public static function isEventSubscriber(ClassReflection $classReflection): bool
    {
        return self::isInEventDirectory($classReflection)
            && $classReflection->getNativeReflection()->isInterface()
            && self::isSubscriberName($classReflection->getName());
    }

    public static function getEventSubscriberName(ClassReflection $classReflection): string
    {
        return $classReflection->getName() . self::SUBSCRIBER_SUFFIX;
    }

    public static function getSubscribedEventName(ClassReflection $classReflection): string
    {
        return substr($classReflection->getName(), 0, -strlen(self::SUBSCRIBER_SUFFIX));
    }

    public static function getExpectedSubscriberMethodName(ClassReflection $classReflection): string
    {
        return 'on' . substr($classReflection->getNativeReflection()->getShortName(), 0, -strlen(self::SUBSCRIBER_SUFFIX));
    }

    private static function isSubscriberName(string $className): bool
    {
        return str_ends_with($className, self::SUBSCRIBER_SUFFIX);
    }
}
