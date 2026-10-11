<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\TestFramework\Coverage\JUnit;

use Infection\TestFramework\PhpUnit\Tracing\JUnit\TestFileDataProvider;
use Infection\Tests\UnsupportedMethod;

final class FakeTestFileDataProvider implements TestFileDataProvider
{
    public function getTestFileInfo(string $testId): never
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }
}
