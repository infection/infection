<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures;

use Infection\Tests\UnsupportedMethod;
use OndraM\CiDetector\Ci\CiInterface;
use OndraM\CiDetector\CiDetector;
use OndraM\CiDetector\Env;

final class DummyCiDetector extends CiDetector
{
    private $ciDetected;

    public function __construct(bool $ciDetected)
    {
        $this->ciDetected = $ciDetected;
    }

    public static function fromEnvironment(Env $environment): CiDetector
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function isCiDetected(): bool
    {
        return $this->ciDetected;
    }

    public function detect(): CiInterface
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }
}
