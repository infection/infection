<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage;

final class TestLocation
{
    private string $method;
    private ?string $filePath;
    private ?float $executionTime;
    public function __construct(string $method, ?string $filePath, ?float $executionTime)
    {
        $this->method = $method;
        $this->filePath = $filePath;
        $this->executionTime = $executionTime;
    }
    public static function forTestMethod(string $testMethod) : self
    {
        return new self($testMethod, null, null);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getFilePath() : ?string
    {
        return $this->filePath;
    }
    public function getExecutionTime() : ?float
    {
        return $this->executionTime;
    }
}
