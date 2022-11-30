<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\OndraM\CiDetector\Ci;

use _HumbugBox9658796bb9f0\OndraM\CiDetector\CiDetector;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\Env;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\TrinaryLogic;
class Codeship extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->get('CI_NAME') === 'codeship';
    }
    public function getCiName() : string
    {
        return CiDetector::CI_CODESHIP;
    }
    public function isPullRequest() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->env->getString('CI_PULL_REQUEST') !== 'false');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('CI_BUILD_NUMBER');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('CI_BUILD_URL');
    }
    public function getCommit() : string
    {
        return $this->env->getString('COMMIT_ID');
    }
    public function getBranch() : string
    {
        return $this->env->getString('CI_BRANCH');
    }
    public function getTargetBranch() : string
    {
        return '';
    }
    public function getRepositoryName() : string
    {
        return $this->env->getString('CI_REPO_NAME');
    }
    public function getRepositoryUrl() : string
    {
        return '';
    }
}
