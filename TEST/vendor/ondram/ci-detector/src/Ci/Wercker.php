<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\OndraM\CiDetector\Ci;

use _HumbugBox9658796bb9f0\OndraM\CiDetector\CiDetector;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\Env;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\TrinaryLogic;
class Wercker extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->get('WERCKER') === 'true';
    }
    public function getCiName() : string
    {
        return CiDetector::CI_WERCKER;
    }
    public function isPullRequest() : TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('WERCKER_RUN_ID');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('WERCKER_RUN_URL');
    }
    public function getCommit() : string
    {
        return $this->env->getString('WERCKER_GIT_COMMIT');
    }
    public function getBranch() : string
    {
        return $this->env->getString('WERCKER_GIT_BRANCH');
    }
    public function getTargetBranch() : string
    {
        return '';
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('WERCKER_GIT_OWNER') . '/' . $this->env->getString('WERCKER_GIT_REPOSITORY');
    }
    public function getRepositoryUrl() : string
    {
        return '';
    }
}
