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

namespace Infection\Configuration\RawConfiguration;

use function file_get_contents;
use function is_file;
use function is_readable;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use stdClass;

final class RawConfiguration
{
    private $path;

    /**
     * @var stdClass|null
     */
    private $decodedContents;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @throws InvalidFile
     */
    public function getDecodedContents(): stdClass
    {
        $this->initDecodedContents();

        return $this->decodedContents;
    }

    /**
     * @throws InvalidFile
     */
    private function initDecodedContents(): void
    {
        if (null !== $this->decodedContents) {
            return;
        }

        if (!is_file($this->path)) {
            throw InvalidFile::createForFileNotFound($this);
        }

        if (!is_readable($this->path)) {
            throw InvalidFile::createForFileNotReadable($this);
        }

        $contents = @file_get_contents($this->path);

        if (false === $contents) {
            throw InvalidFile::createForCouldNotRetrieveFileContents($this);
        }

        try {
            $this->decodedContents = (new JsonParser())->parse(
                $contents,
                JsonParser::DETECT_KEY_CONFLICTS
            );
        } catch (ParsingException $exception) {
            throw InvalidFile::createForInvalidJson($this, $exception->getMessage(), $exception);
        }
    }
}
