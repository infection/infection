<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Tests\UnsupportedMethod;
use OndraM\CiDetector\Ci\CiInterface;
use OndraM\CiDetector\CiDetector;
use OndraM\CiDetector\Env;

final class FakeCiDetector extends CiDetector
{
    public static function fromEnvironment(Env $environment): CiDetector
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function isCiDetected(): bool
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function detect(): CiInterface
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }
}
