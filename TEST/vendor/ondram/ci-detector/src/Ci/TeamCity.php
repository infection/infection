<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\OndraM\CiDetector\Ci;

use _HumbugBox9658796bb9f0\OndraM\CiDetector\CiDetector;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\Env;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\TrinaryLogic;
class TeamCity extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->get('TEAMCITY_VERSION') !== \false;
    }
    public function getCiName() : string
    {
        return CiDetector::CI_TEAMCITY;
    }
    public function isPullRequest() : TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return '';
    }
    public function getCommit() : string
    {
        return $this->env->getString('BUILD_VCS_NUMBER');
    }
    public function getBranch() : string
    {
        return '';
    }
    public function getTargetBranch() : string
    {
        return '';
    }
    public function getRepositoryName() : string
    {
        return '';
    }
    public function getRepositoryUrl() : string
    {
        return '';
    }
}
