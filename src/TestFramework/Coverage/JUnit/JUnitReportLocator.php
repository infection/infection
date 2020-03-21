<?php

declare(strict_types=1);

namespace Infection\TestFramework\Coverage\JUnit;

use Infection\FileSystem\Locator\FileNotFound;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\PathUtil\Path;
use function array_map;
use function count;
use function current;
use function explode;
use function file_exists;
use function implode;
use function iterator_to_array;
use function Safe\sprintf;

final class JUnitReportLocator
{
    private $coveragePath;
    private $defaultJUnitPath;

    /**
     * @var string|null
     */
    private $jUnitPath;

    public function __construct(string $coveragePath, string $defaultJUnitPath)
    {
        $this->coveragePath = Path::canonicalize($coveragePath.'/..');
        $this->defaultJUnitPath = $defaultJUnitPath;
    }

    /**
     * @throws FileNotFound
     */
    public function locate(): string
    {
        if ($this->jUnitPath !== null) {
            return $this->jUnitPath;
        }

        // This is the JUnit path enforced before. It is also the one recommended by the
        // CoverageChecker hence it makes sense to try this one first before attempting any more
        // expensive lookup
        if (file_exists($this->defaultJUnitPath)) {
            return $this->jUnitPath ?? $this->defaultJUnitPath;
        }

        $files = iterator_to_array(
            Finder::create()
                ->files()
                ->in($this->coveragePath)
                ->name('/^(.+\.)?junit\.xml$/i'),
            false
        );

        if (count($files) > 1) {
            throw new FileNotFound(sprintf(
                'Could not locate the JUnit file: more than one file has been found with the'
                .' pattern "*.junit.xml": "%s"',
                implode(
                    '", "',
                    array_map(
                        static function (SplFileInfo $fileInfo): string {
                            return $fileInfo->getPathname();
                        },
                        $files
                    )
                )
            ));
        }

        $junitFileInfo = current($files);

        if ($junitFileInfo !== false) {
            return $this->jUnitPath ?? $junitFileInfo->getPathname();
        }

        throw new FileNotFound(sprintf(
            'Could not find any file with the pattern "*.junit.xml" in "%s"',
            $this->coveragePath
        ));
    }
}
