<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Json;

use function implode;
use function json_decode;
use const JSON_ERROR_NONE;
use const JSON_ERROR_UTF8;
use function json_last_error;
use _HumbugBoxb47773b41c19\JsonSchema\Validator;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\file_contents;
use _HumbugBoxb47773b41c19\Seld\JsonLint\JsonParser;
use _HumbugBoxb47773b41c19\Seld\JsonLint\ParsingException;
use stdClass;
final class Json
{
    private JsonParser $linter;
    public function __construct()
    {
        $this->linter = new JsonParser();
    }
    public function lint(string $json) : void
    {
        $result = $this->linter->lint($json);
        if ($result instanceof ParsingException) {
            throw $result;
        }
    }
    public function decode(string $json, bool $assoc = \false) : array|stdClass
    {
        $data = json_decode($json, $assoc);
        if (JSON_ERROR_NONE !== ($error = json_last_error())) {
            if (JSON_ERROR_UTF8 === $error) {
                throw new ParsingException('JSON decoding failed: Malformed UTF-8 characters, possibly incorrectly encoded');
            }
            $this->lint($json);
        }
        return \false === $assoc ? (object) $data : $data;
    }
    public function decodeFile(string $file, bool $assoc = \false) : array|stdClass
    {
        $json = file_contents($file);
        return $this->decode($json, $assoc);
    }
    public function validate(string $file, stdClass $json, stdClass $schema) : void
    {
        $validator = new Validator();
        $validator->check($json, $schema);
        if (!$validator->isValid()) {
            $errors = [];
            foreach ($validator->getErrors() as $error) {
                $errors[] = ($error['property'] ? $error['property'] . ' : ' : '') . $error['message'];
            }
            $message = [] !== $errors ? "\"{$file}\" does not match the expected JSON schema:\n  - " . implode("\n  - ", $errors) : "\"{$file}\" does not match the expected JSON schema.";
            throw new JsonValidationException($message, $file, $errors);
        }
    }
}
