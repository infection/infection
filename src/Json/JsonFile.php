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

namespace Infection\Json;

use Safe\json_decode;
use Safe\json_last_error_msg;
use Infection\Json\Exception\JsonValidationException;
use Infection\Json\Exception\ParseException;
use JsonSchema\Validator;
use function Safe\file_get_contents;
use stdClass;

/**
 * @internal
 */
final class JsonFile
{
    private const SCHEMA_FILE = __DIR__ . '/../../resources/schema.json';

    private $path;

    /**
     * @var stdClass
     */
    private $data;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function decode(): stdClass
    {
        $this->parse();
        $this->validateSchema();

        return $this->data;
    }

    private function parse(): void
    {
        if (!is_file($this->path)) {
            throw ParseException::invalidJson($this->path, 'file not found');
        }

        $data = json_decode(file_get_contents($this->path));

        if (null === $data && JSON_ERROR_NONE !== json_last_error()) {
            throw ParseException::invalidJson($this->path, json_last_error_msg());
        }

        $this->data = (object) $data;
    }

    private function validateSchema(): void
    {
        $validator = new Validator();

        $schemaFile = self::SCHEMA_FILE;

        // Prepend with file:// only when not using a special schema already (e.g. in the phar)
        if (false === strpos($schemaFile, '://')) {
            $schemaFile = 'file://' . $schemaFile;
        }

        $validator->validate($this->data, (object) ['$ref' => $schemaFile]);

        if (!$validator->isValid()) {
            $errors = [];

            foreach ($validator->getErrors() as $error) {
                $errors[] = ($error['property'] ? $error['property'] . ' : ' : '') . $error['message'];
            }

            throw JsonValidationException::doesNotMatchSchema($this->path, $errors);
        }
    }
}
