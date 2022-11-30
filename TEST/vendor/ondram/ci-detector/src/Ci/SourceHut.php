<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\OndraM\CiDetector\Ci;

use _HumbugBox9658796bb9f0\OndraM\CiDetector\CiDetector;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\Env;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\TrinaryLogic;
class SourceHut extends AbstractCi
{
    public static function isDetected(Env $env) : bool
    {
        return $env->getString('CI_NAME') === 'sourcehut';
    }
    public function getCiName() : string
    {
        return CiDetector::CI_SOURCEHUT;
    }
    public function isPullRequest() : TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean($this->env->getString('BUILD_REASON') === 'patchset');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('JOB_ID');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('JOB_URL');
    }
    public function getCommit() : string
    {
        return '';
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
