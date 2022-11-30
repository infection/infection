<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\OndraM\CiDetector\Ci;

use _HumbugBox9658796bb9f0\OndraM\CiDetector\Env;
use _HumbugBox9658796bb9f0\OndraM\CiDetector\TrinaryLogic;
interface CiInterface
{
    public static function isDetected(Env $env) : bool;
    public function getCiName() : string;
    public function describe() : array;
    public function getBuildNumber() : string;
    public function getBuildUrl() : string;
    public function getCommit() : string;
    public function getBranch() : string;
    public function getTargetBranch() : string;
    public function getRepositoryName() : string;
    public function getRepositoryUrl() : string;
    public function isPullRequest() : TrinaryLogic;
}
