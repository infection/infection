<?php

declare(strict_types=1);

namespace Infection\Configuration\RawConfiguration;

use function error_clear_last;
use function file_get_contents;
use function is_file;
use function is_readable;
use function json_decode;
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