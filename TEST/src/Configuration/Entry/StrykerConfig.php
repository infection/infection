<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Entry;

use InvalidArgumentException;
use function preg_quote;
use _HumbugBox9658796bb9f0\Safe\Exceptions\PcreException;
use function _HumbugBox9658796bb9f0\Safe\preg_match;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
final class StrykerConfig
{
    private string $branchMatch;
    private bool $isForFullReport;
    private function __construct(string $branch, bool $isForFullReport)
    {
        $this->isForFullReport = $isForFullReport;
        if (preg_match('#^/.+/$#', $branch) === 0) {
            $this->branchMatch = '/^' . preg_quote($branch, '/') . '$/';
            return;
        }
        try {
            @preg_match($branch, '');
        } catch (PcreException $invalidRegex) {
            throw new InvalidArgumentException(sprintf('Provided branchMatchRegex "%s" is not a valid regex', $branch), 0, $invalidRegex);
        }
        $this->branchMatch = $branch;
    }
    public static function forBadge(string $branch) : self
    {
        return new self($branch, \false);
    }
    public static function forFullReport(string $branch) : self
    {
        return new self($branch, \true);
    }
    public function isForFullReport() : bool
    {
        return $this->isForFullReport;
    }
    public function applicableForBranch(string $branchName) : bool
    {
        return preg_match($this->branchMatch, $branchName) === 1;
    }
}
