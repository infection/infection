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
use function is_scalar;
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
 * Type: scalar_or_object<T>
 *
 * Scalar values (int, string, bool, float) pass through as-is.
 * Arrays/objects deserialize to the specified generic type T.
 *
 * Type validation happens elsewhere:
 * - Schema validation (before deserialization)
 * - PHP typed properties (after deserialization)
 *
 * @internal
 */
final class ScalarOrObjectHandler implements SubscribingHandlerInterface
{
    /**
     * @return array<array<string, int|string>>
     */
    public static function getSubscribingMethods(): array
    {
        $formats = ['json'];
        $types = ['scalar_or_object'];
        $methods = [];

        foreach ($types as $type) {
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

    /**
     * @param array<string, mixed> $type
     *
     * @phpstan-ignore-next-line shipmonk.deadMethod (called dynamically by JMS)
     */
    public function deserialize(
        DeserializationVisitorInterface $visitor,
        mixed $data,
        array $type,
        Context $context,
    ): mixed {
        if (is_scalar($data)) {
            return $data;
        }

        if (is_array($data)) {
            if (!isset($type['params'][0]['name'])) {
                return $data;
            }

            $targetType = $type['params'][0];

            return $context->getNavigator()->accept($data, $targetType);
        }

        throw new UnexpectedValueException(
            sprintf('Expected scalar or array, got "%s".', get_debug_type($data)),
        );
    }

    /**
     * @param array<string, mixed> $type
     *
     * @phpstan-ignore-next-line shipmonk.deadMethod (called dynamically by JMS)
     */
    public function serialize(
        SerializationVisitorInterface $visitor,
        mixed $data,
        array $type,
        Context $context,
    ): mixed {
        if (is_scalar($data)) {
            return $data;
        }

        if (!isset($type['params'][0]['name'])) {
            return $data;
        }

        $targetType = $type['params'][0];

        return $context->getNavigator()->accept($data, $targetType);
    }
}
