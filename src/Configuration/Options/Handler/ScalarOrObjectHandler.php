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

namespace Infection\Configuration\Options\Handler;

use function get_debug_type;
use function is_array;
use function is_bool;
use function is_int;
use function is_scalar;
use function is_string;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use function sprintf;
use UnexpectedValueException;

/**
 * Handles union types "scalar|object" for JMS Serializer.
 *
 * Supports custom type names:
 * - int_or_string: int|string (for threads: integer or "max")
 * - bool_or_object: bool|object (for mutator configs)
 *
 * @internal
 */
final class ScalarOrObjectHandler implements SubscribingHandlerInterface
{
    private const TYPE_INT_OR_STRING = 'int_or_string';

    private const TYPE_BOOL_OR_OBJECT = 'bool_or_object';

    public static function getSubscribingMethods(): array
    {
        $formats = ['json'];
        $methods = [];

        foreach ([self::TYPE_INT_OR_STRING, self::TYPE_BOOL_OR_OBJECT] as $type) {
            foreach ($formats as $format) {
                $methods[] = [
                    'type' => $type,
                    'format' => $format,
                    'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                    'method' => 'deserialize',
                ];
                $methods[] = [
                    'type' => $type,
                    'format' => $format,
                    'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                    'method' => 'serialize',
                ];
            }
        }

        return $methods;
    }

    public function deserialize(
        DeserializationVisitorInterface $visitor,
        mixed $data,
        array $type,
        Context $context,
    ): mixed {
        $typeName = $type['name'];

        if ($typeName === self::TYPE_INT_OR_STRING) {
            return $this->deserializeIntOrString($data);
        }

        if ($typeName === self::TYPE_BOOL_OR_OBJECT) {
            return $this->deserializeBoolOrObject($visitor, $data, $type, $context);
        }

        throw new UnexpectedValueException(sprintf('Unsupported type "%s".', $typeName));
    }

    public function serialize(
        SerializationVisitorInterface $visitor,
        mixed $data,
        array $type,
        Context $context,
    ): mixed {
        $typeName = $type['name'];

        if ($typeName === self::TYPE_INT_OR_STRING) {
            return $data;
        }

        if ($typeName === self::TYPE_BOOL_OR_OBJECT) {
            if (is_scalar($data)) {
                return $data;
            }

            if (empty($type['params'][0]['name'] ?? null)) {
                throw new UnexpectedValueException(
                    sprintf('%s<T> requires a generic type parameter.', $typeName),
                );
            }

            $targetType = $type['params'][0];

            return $context->getNavigator()->accept($data, $targetType);
        }

        throw new UnexpectedValueException(sprintf('Unsupported type "%s".', $typeName));
    }

    private function deserializeIntOrString(mixed $data): int|string
    {
        if (is_int($data)) {
            return $data;
        }

        if (is_string($data)) {
            return $data;
        }

        throw new UnexpectedValueException(
            sprintf('Expected int or string, got "%s".', get_debug_type($data)),
        );
    }

    private function deserializeBoolOrObject(
        DeserializationVisitorInterface $visitor,
        mixed $data,
        array $type,
        Context $context,
    ): mixed {
        if (is_bool($data)) {
            return $data;
        }

        if (is_array($data)) {
            if (empty($type['params'][0]['name'] ?? null)) {
                throw new UnexpectedValueException(
                    sprintf('%s<T> requires a generic type parameter.', self::TYPE_BOOL_OR_OBJECT),
                );
            }

            $targetType = $type['params'][0];

            return $context->getNavigator()->accept($data, $targetType);
        }

        throw new UnexpectedValueException(
            sprintf('Expected bool or object, got "%s".', get_debug_type($data)),
        );
    }
}
