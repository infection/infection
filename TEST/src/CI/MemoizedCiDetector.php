<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\CI;

use _HumbugBox9658796bb9f0\OndraM\CiDetector\Ci\CiInterface;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\CiDetector;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\Env;
use ReflectionClass;
final class MemoizedCiDetector extends CiDetector
{
    private $ci = \false;
    public static function fromEnvironment(Env $environment) : CiDetector
    {
        $detector = new self();
        $environmentReflection = (new ReflectionClass(CiDetector::class))->getProperty('environment');
        $environmentReflection->setAccessible(\true);
        $environmentReflection->setValue($detector, $environment);
        return $detector;
    }
    protected function detectCurrentCiServer() : ?CiInterface
    {
        if ($this->ci === \false) {
            $this->ci = parent::detectCurrentCiServer();
        }
        return $this->ci;
    }
}
