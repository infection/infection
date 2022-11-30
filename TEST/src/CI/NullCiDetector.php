<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\CI;

use _HumbugBox9658796bb9f0\OndraM\CiDetector\Ci\CiInterface;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\CiDetector;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\Env;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\Exception\CiNotDetectedException;
final class NullCiDetector extends CiDetector
{
    public static function fromEnvironment(Env $environment) : CiDetector
    {
        return new self();
    }
    public function isCiDetected() : bool
    {
        return \false;
    }
    public function detect() : CiInterface
    {
        throw new CiNotDetectedException('No CI server detectable with this detector');
    }
}
