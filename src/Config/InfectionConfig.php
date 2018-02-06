<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Config;

use Infection\Mutator\Arithmetic\BitwiseAnd;
use Infection\Mutator\Arithmetic\BitwiseNot;
use Infection\Mutator\Arithmetic\BitwiseOr;
use Infection\Mutator\Arithmetic\BitwiseXor;
use Infection\Mutator\Arithmetic\Decrement;
use Infection\Mutator\Arithmetic\DivEqual;
use Infection\Mutator\Arithmetic\Division;
use Infection\Mutator\Arithmetic\Exponentiation;
use Infection\Mutator\Arithmetic\Increment;
use Infection\Mutator\Arithmetic\Minus;
use Infection\Mutator\Arithmetic\MinusEqual;
use Infection\Mutator\Arithmetic\ModEqual;
use Infection\Mutator\Arithmetic\Modulus;
use Infection\Mutator\Arithmetic\MulEqual;
use Infection\Mutator\Arithmetic\Multiplication;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Arithmetic\PlusEqual;
use Infection\Mutator\Arithmetic\PowEqual;
use Infection\Mutator\Arithmetic\ShiftLeft;
use Infection\Mutator\Arithmetic\ShiftRight;
use Infection\Mutator\Boolean\FalseValue;
use Infection\Mutator\Boolean\LogicalAnd;
use Infection\Mutator\Boolean\LogicalLowerAnd;
use Infection\Mutator\Boolean\LogicalLowerOr;
use Infection\Mutator\Boolean\LogicalNot;
use Infection\Mutator\Boolean\LogicalOr;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\ConditionalBoundary\GreaterThan;
use Infection\Mutator\ConditionalBoundary\GreaterThanOrEqualTo;
use Infection\Mutator\ConditionalBoundary\LessThan;
use Infection\Mutator\ConditionalBoundary\LessThanOrEqualTo;
use Infection\Mutator\ConditionalNegotiation\Equal;
use Infection\Mutator\ConditionalNegotiation\GreaterThanNegotiation;
use Infection\Mutator\ConditionalNegotiation\GreaterThanOrEqualToNegotiation;
use Infection\Mutator\ConditionalNegotiation\Identical;
use Infection\Mutator\ConditionalNegotiation\LessThanNegotiation;
use Infection\Mutator\ConditionalNegotiation\LessThanOrEqualToNegotiation;
use Infection\Mutator\ConditionalNegotiation\NotEqual;
use Infection\Mutator\ConditionalNegotiation\NotIdentical;
use Infection\Mutator\FunctionSignature\ProtectedVisibility;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Number\DecrementInteger;
use Infection\Mutator\Number\IncrementInteger;
use Infection\Mutator\Number\OneZeroFloat;
use Infection\Mutator\Number\OneZeroInteger;
use Infection\Mutator\Operator\Break_;
use Infection\Mutator\Operator\Continue_;
use Infection\Mutator\ReturnValue\FloatNegation;
use Infection\Mutator\ReturnValue\FunctionCall;
use Infection\Mutator\ReturnValue\IntegerNegation;
use Infection\Mutator\ReturnValue\NewObject;
use Infection\Mutator\ReturnValue\This;
use Infection\Mutator\Sort\Spaceship;
use Infection\Mutator\ZeroIteration\Foreach_;
use Symfony\Component\Filesystem\Filesystem;

class InfectionConfig
{
    const PROCESS_TIMEOUT_SECONDS = 10;
    const DEFAULT_SOURCE_DIRS = ['.'];
    const DEFAULT_EXCLUDE_DIRS = ['vendor'];
    const CONFIG_FILE_NAME = 'infection.json';

    const DEFAULT_MUTATORS = [
        // Arithmetic
        BitwiseAnd::class,
        BitwiseNot::class,
        BitwiseOr::class,
        BitwiseXor::class,
        Decrement::class,
        DivEqual::class,
        Division::class,
        Exponentiation::class,
        Increment::class,
        Minus::class,
        MinusEqual::class,
        ModEqual::class,
        Modulus::class,
        MulEqual::class,
        Multiplication::class,
        Plus::class,
        PlusEqual::class,
        PowEqual::class,
        ShiftLeft::class,
        ShiftRight::class,

        // Boolean
        FalseValue::class,
        LogicalAnd::class,
        LogicalLowerAnd::class,
        LogicalLowerOr::class,
        LogicalNot::class,
        LogicalOr::class,
        TrueValue::class,

        // Conditional Boundary
        GreaterThan::class,
        GreaterThanOrEqualTo::class,
        LessThan::class,
        LessThanOrEqualTo::class,

        // Conditional Negotiation
        Equal::class,
        GreaterThanNegotiation::class,
        GreaterThanOrEqualToNegotiation::class,
        Identical::class,
        LessThanNegotiation::class,
        LessThanOrEqualToNegotiation::class,
        NotEqual::class,
        NotIdentical::class,

        // Number
        DecrementInteger::class,
        IncrementInteger::class,
        OneZeroInteger::class,
        OneZeroFloat::class,

        // Return Value
        FloatNegation::class,
        FunctionCall::class,
        IntegerNegation::class,
        NewObject::class,
        This::class,

        // Function Signature
        PublicVisibility::class,
        ProtectedVisibility::class,

        // Sort
        Spaceship::class,

        Break_::class,
        Continue_::class,
        Foreach_::class,
    ];

    /**
     * @var \stdClass
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(\stdClass $config, Filesystem $filesystem)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
    }

    public function getPhpUnitConfigDir(): string
    {
        if (isset($this->config->phpUnit->configDir)) {
            return getcwd() . DIRECTORY_SEPARATOR . $this->config->phpUnit->configDir;
        }

        return getcwd();
    }

    public function getPhpUnitCustomPath()
    {
        return $this->config->phpUnit->customPath ?? null;
    }

    public function getProcessTimeout(): int
    {
        return $this->config->timeout ?? self::PROCESS_TIMEOUT_SECONDS;
    }

    public function getSourceDirs(): array
    {
        return $this->config->source->directories ?? self::DEFAULT_SOURCE_DIRS;
    }

    public function getSourceExcludePaths(): array
    {
        $originalExcludedPaths = $this->getExcludes();
        $excludedPaths = [];

        foreach ($originalExcludedPaths as $originalExcludedPath) {
            if (strpos($originalExcludedPath, '*') === false) {
                $excludedPaths[] = $originalExcludedPath;
            } else {
                $excludedPaths = array_merge(
                    $excludedPaths,
                    $this->getExcludedDirsByPattern($originalExcludedPath)
                );
            }
        }

        return $excludedPaths;
    }

    private function getExcludes(): array
    {
        if (isset($this->config->source->excludes) && is_array($this->config->source->excludes)) {
            return $this->config->source->excludes;
        }

        return self::DEFAULT_EXCLUDE_DIRS;
    }

    /**
     * @return string|null
     */
    public function getLogPathInfoFor(string $log)
    {
        return $this->config->logs->$log ?? null;
    }

    public function getLogsTypes(): array
    {
        return (array) $this->config->logs ?? [];
    }

    private function getExcludedDirsByPattern(string $originalExcludedDir)
    {
        $excludedDirs = [];
        $srcDirs = $this->getSourceDirs();

        foreach ($srcDirs as $srcDir) {
            $unpackedPaths = glob(
                sprintf('%s/%s', $srcDir, $originalExcludedDir),
                GLOB_ONLYDIR
            );

            if ($unpackedPaths) {
                $excludedDirs = array_merge(
                    $excludedDirs,
                    array_map(
                        function ($excludeDir) use ($srcDir) {
                            return ltrim(
                                substr_replace($excludeDir, '', 0, strlen($srcDir)),
                                DIRECTORY_SEPARATOR
                            );
                        },
                        $unpackedPaths
                    )
                );
            }
        }

        return $excludedDirs;
    }

    public function getTmpDir(): string
    {
        if (empty($this->config->tmpDir)) {
            return sys_get_temp_dir();
        }

        $tmpDir = $this->config->tmpDir;

        if ($this->filesystem->isAbsolutePath($tmpDir)) {
            return $tmpDir;
        }

        return sprintf('%s/%s', getcwd(), $tmpDir);
    }
}
