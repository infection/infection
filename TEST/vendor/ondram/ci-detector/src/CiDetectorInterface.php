<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\OndraM\CiDetector;

use _HumbugBox9658796bb9f0\OndraM\CiDetector\Ci\CiInterface;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\Exception\CiNotDetectedException;
interface CiDetectorInterface
{
    public function isCiDetected() : bool;
    public function detect() : CiInterface;
}
