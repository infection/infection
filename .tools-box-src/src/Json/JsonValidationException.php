<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Json;

use Throwable;
use UnexpectedValueException;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class JsonValidationException extends UnexpectedValueException
{
    private $validatedFile;
    private $errors;
    public function __construct(string $message, ?string $file = null, array $errors = [], int $code = 0, ?Throwable $previous = null)
    {
        if (null !== $file) {
            Assert::file($file);
        }
        Assert::allString($errors);
        $this->validatedFile = $file;
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }
    public function getValidatedFile() : ?string
    {
        return $this->validatedFile;
    }
    public function getErrors() : array
    {
        return $this->errors;
    }
}
