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

use InvalidArgumentException;
use function is_object;
use function is_string;
use function method_exists;

trait ClassReflectionAccessor
{
    private function getClassReflectionName(mixed $classReflection): string
    {
        if (!is_object($classReflection) || !method_exists($classReflection, 'getName')) {
            throw new InvalidArgumentException('Expected a class reflection with a getName() method.');
        }

        return self::toString(
            $classReflection->getName(),
            'Expected the class reflection name to be a string.',
        );
    }

    private function getClassReflectionFileName(mixed $classReflection): ?string
    {
        if (!is_object($classReflection) || !method_exists($classReflection, 'getFileName')) {
            throw new InvalidArgumentException('Expected a class reflection with a getFileName() method.');
        }

        return self::toOptionalString(
            $classReflection->getFileName(),
            'Expected the class reflection file name to be null or a string.',
        );
    }

    private static function toString(mixed $value, string $errorMessage): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException($errorMessage);
        }

        return $value;
    }

    private static function toOptionalString(mixed $value, string $errorMessage): ?string
    {
        if ($value === null || is_string($value)) {
            return $value;
        }

        throw new InvalidArgumentException($errorMessage);
    }
}
