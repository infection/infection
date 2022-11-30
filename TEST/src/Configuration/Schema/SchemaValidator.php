<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Schema;

use function array_map;
use _HumbugBox9658796bb9f0\JsonSchema\Validator;
use const PHP_EOL;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function strpos;
class SchemaValidator
{
    private const SCHEMA_FILE = __DIR__ . '/../../../resources/schema.json';
    public function validate(SchemaConfigurationFile $rawConfig) : void
    {
        $validator = new Validator();
        $schemaFile = self::SCHEMA_FILE;
        if (strpos($schemaFile, '://') === \false) {
            $schemaFile = 'file://' . $schemaFile;
        }
        $contents = $rawConfig->getDecodedContents();
        $validator->validate($contents, (object) ['$ref' => $schemaFile]);
        if ($validator->isValid()) {
            return;
        }
        $errors = array_map(static function (array $error) : string {
            return sprintf('[%s] %s%s', $error['property'], $error['message'], PHP_EOL);
        }, $validator->getErrors());
        throw InvalidSchema::create($rawConfig, $errors);
    }
}
