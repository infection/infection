<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\SebastianBergmann\Diff\Output;

use function count;
abstract class AbstractChunkOutputBuilder implements DiffOutputBuilderInterface
{
    protected function getCommonChunks(array $diff, int $lineThreshold = 5) : array
    {
        $diffSize = count($diff);
        $capturing = \false;
        $chunkStart = 0;
        $chunkSize = 0;
        $commonChunks = [];
        for ($i = 0; $i < $diffSize; ++$i) {
            if ($diff[$i][1] === 0) {
                if ($capturing === \false) {
                    $capturing = \true;
                    $chunkStart = $i;
                    $chunkSize = 0;
                } else {
                    ++$chunkSize;
                }
            } elseif ($capturing !== \false) {
                if ($chunkSize >= $lineThreshold) {
                    $commonChunks[$chunkStart] = $chunkStart + $chunkSize;
                }
                $capturing = \false;
            }
        }
        if ($capturing !== \false && $chunkSize >= $lineThreshold) {
            $commonChunks[$chunkStart] = $chunkStart + $chunkSize;
        }
        return $commonChunks;
    }
}
