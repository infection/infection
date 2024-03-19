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

namespace Infection\Configuration\Schema;

use function sprintf;
use Throwable;
use UnexpectedValueException;

/**
 * @internal
 */
final class InvalidFile extends UnexpectedValueException
{
    public static function createForFileNotFound(SchemaConfigurationFile $config): self
    {
        return new self(sprintf(
            'The file "%s" could not be found or is not a file.',
            $config->getPath(),
        ));
    }

    public static function createForFileNotReadable(SchemaConfigurationFile $config): self
    {
        return new self(sprintf(
            'The file "%s" is not readable.',
            $config->getPath(),
        ));
    }

    public static function createForCouldNotRetrieveFileContents(SchemaConfigurationFile $config): self
    {
        return new self(sprintf(
            'Could not retrieve the contents of the file "%s".',
            $config->getPath(),
        ));
    }

    public static function createForInvalidJson(
        SchemaConfigurationFile $config,
        string $error,
        Throwable $previous,
    ): self {
        return new self(
            sprintf(
                'Could not parse the JSON file "%s": %s',
                $config->getPath(),
                $error,
            ),
            0,
            $previous,
        );
    }
}
