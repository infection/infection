<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework;

use function _HumbugBox9658796bb9f0\Safe\preg_replace;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function trim;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class TestFrameworkExtraOptionsFilter
{
    public function filterForMutantProcess(string $actualExtraOptions, array $initialRunOnlyOptions) : string
    {
        foreach ($initialRunOnlyOptions as $initialRunOnlyOption) {
            $actualExtraOptions = preg_replace(sprintf('/%s[\\=| ](?:\\"[^\\"]*\\"|\'[^\']*\'|[^\\ ]*)/', $initialRunOnlyOption), '', $actualExtraOptions);
            Assert::notNull($actualExtraOptions);
        }
        return preg_replace('/\\s+/', ' ', trim($actualExtraOptions));
    }
}
