<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Schema;

use _HumbugBox9658796bb9f0\ColinODell\Json5\SyntaxError;
use function is_file;
use function is_readable;
use function _HumbugBox9658796bb9f0\json5_decode;
use function _HumbugBox9658796bb9f0\Safe\file_get_contents;
use stdClass;
final class SchemaConfigurationFile
{
    private string $path;
    private ?stdClass $decodedContents = null;
    public function __construct(string $path)
    {
        $this->path = $path;
    }
    public function getPath() : string
    {
        return $this->path;
    }
    public function getDecodedContents() : stdClass
    {
        if ($this->decodedContents instanceof stdClass) {
            return $this->decodedContents;
        }
        if (!is_file($this->path)) {
            throw InvalidFile::createForFileNotFound($this);
        }
        if (!is_readable($this->path)) {
            throw InvalidFile::createForFileNotReadable($this);
        }
        $contents = file_get_contents($this->path);
        try {
            return $this->decodedContents = json5_decode($contents);
        } catch (SyntaxError $exception) {
            throw InvalidFile::createForInvalidJson($this, $exception->getMessage(), $exception);
        }
    }
}
