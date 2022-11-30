<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\OndraM\CiDetector\Ci;

use _HumbugBox9658796bb9f0\OndraM\CiDetector\CiDetector;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\Env;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\TrinaryLogic;
class Circle extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->get('CIRCLECI') !== \false;
    }
    public function getCiName() : string
    {
        return CiDetector::CI_CIRCLE;
    }
    public function isPullRequest() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->env->getString('CI_PULL_REQUEST') !== '');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('CIRCLE_BUILD_NUM');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('CIRCLE_BUILD_URL');
    }
    public function getCommit() : string
    {
        return $this->env->getString('CIRCLE_SHA1');
    }
    public function getBranch() : string
    {
        return $this->env->getString('CIRCLE_BRANCH');
    }
    public function getTargetBranch() : string
    {
        return '';
    }
    public function getRepositoryName() : string
    {
        return \sprintf('%s/%s', $this->env->getString('CIRCLE_PROJECT_USERNAME'), $this->env->getString('CIRCLE_PROJECT_REPONAME'));
    }
    public function getRepositoryUrl() : string
    {
        return $this->env->getString('CIRCLE_REPOSITORY_URL');
    }
}
