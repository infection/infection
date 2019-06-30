<?php

declare(strict_types=1);

namespace Infection\Configuration\Schema;

use function array_map;
use Infection\Configuration\JsonValidationException;
use Infection\Configuration\RawConfiguration\RawConfiguration;
use JsonSchema\Validator;
use const PHP_EOL;
use stdClass;

final class SchemaValidator
{
    private const SCHEMA_FILE = __DIR__ . '/../../../resources/schema.json';

    /**
     * @throws InvalidSchema
     */
    public function validate(RawConfiguration $rawConfig): void
    {
        $validator = new Validator();

        $schemaFile = self::SCHEMA_FILE;

        // Prepend with file:// only when not using a special schema already (e.g. in the PHAR)
        if (false === strpos($schemaFile, '://')) {
            $schemaFile = 'file://' . $schemaFile;
        }

        $contents = $rawConfig->getContents();

        $validator->validate($contents, (object) ['$ref' => $schemaFile]);

        if ($validator->isValid()) {
            return;
        }

        $errors = array_map(
            static function (array $error): string
            {
                return sprintf('[%s] %s%s', $error['property'], $error['message'], PHP_EOL);
            },
            $validator->getErrors()
        );

        throw InvalidSchema::create($rawConfig, $errors);
    }
}