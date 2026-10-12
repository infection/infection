<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\PHPUnit;

use Symfony\Component\Filesystem\Filesystem;
use function array_map;
use function hash;
use function is_file;

final readonly class CacheSnapshot
{
    public function __construct(
        private Filesystem $filesystem,
    ) {
    }

    /**
     * @param array<string, string|null> $paths
     * @return array<string, string|null>
     */
    public function read(array $paths): array
    {
        return array_map(
            fn (?string $path): ?string => $path === null || !is_file($path)
                ? null
                : hash(
                    'sha256',
                    $this->filesystem->readFile($path),
                ),
            $paths,
        );
    }
}
