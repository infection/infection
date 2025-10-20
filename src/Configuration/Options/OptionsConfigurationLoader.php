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

namespace Infection\Configuration\Options;

use ColinODell\Json5\SyntaxError;
use Infection\Configuration\Schema\InvalidFile;
use Infection\Configuration\Schema\SchemaConfigurationFile;
use Infection\Configuration\Schema\SchemaValidator;
use function is_file;
use function is_readable;
use function json5_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;
use function Safe\file_get_contents;

/**
 * Loads configuration from infection.json/json5 files into InfectionOptions.
 *
 * @internal
 * @final
 */
class OptionsConfigurationLoader
{
    public function __construct(
        private readonly SchemaValidator $schemaValidator,
        private readonly InfectionConfigDeserializer $deserializer,
    ) {
    }

    public function load(string $configFile): InfectionOptions
    {
        $rawConfigFile = new SchemaConfigurationFile($configFile);

        // Validate against JSON schema
        $this->schemaValidator->validate($rawConfigFile);

        if (!is_file($configFile)) {
            throw InvalidFile::createForFileNotFound($rawConfigFile);
        }

        if (!is_readable($configFile)) {
            throw InvalidFile::createForFileNotReadable($rawConfigFile);
        }

        $contents = file_get_contents($configFile);

        try {
            // Decode JSON5 (also handles plain JSON)
            $decoded = json5_decode($contents);

            // Convert to pure JSON string for JMS Serializer
            $jsonString = json_encode($decoded, JSON_THROW_ON_ERROR);

            // Deserialize to InfectionOptions
            return $this->deserializer->deserialize($jsonString);
        } catch (SyntaxError $exception) {
            throw InvalidFile::createForInvalidJson($rawConfigFile, $exception->getMessage(), $exception);
        }
    }
}
