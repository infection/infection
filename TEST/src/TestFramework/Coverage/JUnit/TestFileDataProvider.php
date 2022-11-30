<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit;

interface TestFileDataProvider
{
    public function getTestFileInfo(string $fullyQualifiedClassName) : TestFileTimeData;
}
