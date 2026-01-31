<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\TestFramework\Coverage\JUnit;

use Infection\TestFramework\Coverage\JUnit\TestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\TestFileTimeData;
use Infection\Tests\UnsupportedMethod;

final class FakeTestFileDataProvider implements TestFileDataProvider
{
    public function getTestFileInfo(string $fullyQualifiedClassName): never
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }
}
