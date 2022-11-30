<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutant;

use function array_keys;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\Mutator\ProfileList;
use _HumbugBox9658796bb9f0\Later\Interfaces\Deferred;
use RuntimeException;
use function strlen;
use function strrpos;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class MutantExecutionResult
{
    private string $detectionStatus;
    private string $mutatorName;
    public function __construct(private string $processCommandLine, private string $processOutput, string $detectionStatus, private Deferred $mutantDiff, private string $mutantHash, string $mutatorName, private string $originalFilePath, private int $originalStartingLine, private int $originalEndingLine, private int $originalStartFilePosition, private int $originalEndFilePosition, private Deferred $originalCode, private Deferred $mutatedCode, private array $tests)
    {
        Assert::oneOf($detectionStatus, DetectionStatus::ALL);
        Assert::oneOf($mutatorName, array_keys(ProfileList::ALL_MUTATORS));
        $this->detectionStatus = $detectionStatus;
        $this->mutatorName = $mutatorName;
    }
    public static function createFromNonCoveredMutant(Mutant $mutant) : self
    {
        return self::createFromMutant($mutant, DetectionStatus::NOT_COVERED);
    }
    public static function createFromTimeSkippedMutant(Mutant $mutant) : self
    {
        return self::createFromMutant($mutant, DetectionStatus::SKIPPED);
    }
    public static function createFromIgnoredMutant(Mutant $mutant) : self
    {
        return self::createFromMutant($mutant, DetectionStatus::IGNORED);
    }
    public function getProcessCommandLine() : string
    {
        return $this->processCommandLine;
    }
    public function getProcessOutput() : string
    {
        return $this->processOutput;
    }
    public function getDetectionStatus() : string
    {
        return $this->detectionStatus;
    }
    public function getMutantDiff() : string
    {
        return $this->mutantDiff->get();
    }
    public function getMutantHash() : string
    {
        return $this->mutantHash;
    }
    public function getMutatorName() : string
    {
        return $this->mutatorName;
    }
    public function getOriginalFilePath() : string
    {
        return $this->originalFilePath;
    }
    public function getOriginalStartingLine() : int
    {
        return $this->originalStartingLine;
    }
    public function getOriginalEndingLine() : int
    {
        return $this->originalEndingLine;
    }
    public function getOriginalStartingColumn(string $originalCode) : int
    {
        return $this->toColumn($originalCode, $this->originalStartFilePosition);
    }
    public function getOriginalEndingColumn(string $originalCode) : int
    {
        return $this->toColumn($originalCode, $this->originalEndFilePosition);
    }
    public function getOriginalCode() : string
    {
        return $this->originalCode->get();
    }
    public function getMutatedCode() : string
    {
        return $this->mutatedCode->get();
    }
    public function getTests() : array
    {
        return $this->tests;
    }
    private function toColumn(string $code, int $position) : int
    {
        if ($position > strlen($code)) {
            throw new RuntimeException('Invalid position information');
        }
        $lineStartPos = strrpos($code, "\n", $position - strlen($code));
        if ($lineStartPos === \false) {
            $lineStartPos = -1;
        }
        return $position - $lineStartPos;
    }
    private static function createFromMutant(Mutant $mutant, string $detectionStatus) : self
    {
        $mutation = $mutant->getMutation();
        return new self('', '', $detectionStatus, $mutant->getDiff(), $mutant->getMutation()->getHash(), $mutant->getMutation()->getMutatorName(), $mutation->getOriginalFilePath(), $mutation->getOriginalStartingLine(), $mutation->getOriginalEndingLine(), $mutation->getOriginalStartFilePosition(), $mutation->getOriginalEndFilePosition(), $mutant->getPrettyPrintedOriginalCode(), $mutant->getMutatedCode(), $mutant->getTests());
    }
}
