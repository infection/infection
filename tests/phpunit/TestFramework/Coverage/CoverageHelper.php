<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Coverage;

use Infection\TestFramework\Coverage\CoverageFileData;
use function is_array;
use function is_scalar;

final class CoverageHelper
{
    /**
     * @param array<string, CoverageFileData> $coverage
     *
     * @return array<string, mixed>
     */
    public static function convertToArray(array $coverage): array
    {
        return self::serializeValue($coverage);
    }

    private static function serializeValue($mixed)
    {
        if ($mixed === null) {
            return null;
        }

        if (is_scalar($mixed)) {
            return $mixed;
        }

        if (is_array($mixed)) {
            $convertedArray = [];

            foreach ($mixed as $key => $value) {
                $convertedArray[$key] = self::serializeValue($value);
            }

            return $convertedArray;
        }

        return self::serializeValue((array) $mixed);
    }

    private function __construct()
    {

    }
}
