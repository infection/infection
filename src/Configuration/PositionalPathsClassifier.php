<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Configuration;

use function basename;
use function class_exists;
use function count;
use function dirname;
use function implode;
use Infection\CannotBeInstantiated;
use Infection\Command\Option\PathsArgument;
use Infection\Command\Option\SourceFilterOptions;
use Infection\Command\Option\TestFrameworkExtraArgsOption;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\FileSystem\FileSystem;
use InvalidArgumentException;
use function rtrim;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strtoupper;
use Symfony\Component\Filesystem\Path;

/**
 * Classifies the positional `path` / `secondary-path` slots into source-filter
 * and test-framework-extra-args buckets
 *
 * @internal
 */
final class PositionalPathsClassifier
{
    use CannotBeInstantiated;

    private const string KIND_SOURCE = 'source';

    private const string KIND_TEST = 'test';

    /**
     * @param list<non-empty-string> $slot1
     * @param list<non-empty-string> $slot2
     */
    public static function fromSlots(
        array $slot1,
        array $slot2,
        SchemaConfiguration $schema,
        FileSystem $fileSystem,
    ): ClassifiedPaths {
        if ($slot1 === [] && $slot2 === []) {
            return new ClassifiedPaths([], null);
        }

        $configDir = dirname($schema->pathname);
        $absoluteSourceDirs = self::resolveAbsoluteSourceDirectories($schema, $configDir);

        $slot1Kind = self::classifySlot($slot1, $absoluteSourceDirs, $configDir, $fileSystem, PathsArgument::SLOT_1_NAME);
        $slot2Kind = self::classifySlot($slot2, $absoluteSourceDirs, $configDir, $fileSystem, PathsArgument::SLOT_2_NAME);

        self::assertTestSlotIsSinglePath($slot1Kind, $slot1, PathsArgument::SLOT_1_NAME);
        self::assertTestSlotIsSinglePath($slot2Kind, $slot2, PathsArgument::SLOT_2_NAME);

        if ($slot1Kind !== null && $slot2Kind !== null && $slot1Kind === $slot2Kind) {
            $hint = $slot1Kind === self::KIND_SOURCE
                ? sprintf(' Combine same-kind source paths with commas in a single argument (e.g. "%s") instead of using two slots.', implode(',', [...$slot1, ...$slot2]))
                : ' Pass at most one test path as a positional argument; both slots cannot resolve to test paths.';

            throw new InvalidArgumentException(sprintf(
                'Both positional arguments resolved to %s paths.%s',
                $slot1Kind,
                $hint,
            ));
        }

        $sourcePaths = [];
        $testPath = null;

        if ($slot1Kind === self::KIND_SOURCE) {
            $sourcePaths = $slot1;
        } elseif ($slot1Kind === self::KIND_TEST) {
            // assertTestSlotIsSinglePath above guarantees count($slot1) === 1.
            $testPath = $slot1[0];
        }

        if ($slot2Kind === self::KIND_SOURCE) {
            $sourcePaths = $slot2;
        } elseif ($slot2Kind === self::KIND_TEST) {
            // assertTestSlotIsSinglePath above guarantees count($slot2) === 1.
            $testPath = $slot2[0];
        }

        return new ClassifiedPaths($sourcePaths, $testPath);
    }

    /**
     * Comma-separated lists are allowed only for source paths ("--filter" analogue).
     *
     * @param self::KIND_*|null $kind
     * @param list<non-empty-string> $slot
     */
    private static function assertTestSlotIsSinglePath(?string $kind, array $slot, string $slotName): void
    {
        if ($kind === self::KIND_TEST && count($slot) > 1) {
            throw new InvalidArgumentException(sprintf(
                'The "<%s>" argument lists multiple test paths separated by commas. Test paths must be a single file or directory; comma-separated test paths are not supported.',
                $slotName,
            ));
        }
    }

    /**
     * @param list<non-empty-string> $slot
     * @param list<non-empty-string> $absoluteSourceDirs
     *
     * @return 'source'|'test'|null
     */
    private static function classifySlot(
        array $slot,
        array $absoluteSourceDirs,
        string $configDir,
        FileSystem $fileSystem,
        string $slotName,
    ): ?string {
        if ($slot === []) {
            return null;
        }

        $kind = null;

        foreach ($slot as $path) {
            $itemKind = self::classifyPathKind($path, $absoluteSourceDirs, $configDir, $fileSystem);

            if ($kind === null) {
                $kind = $itemKind;

                continue;
            }

            if ($kind !== $itemKind) {
                throw new InvalidArgumentException(sprintf(
                    'The "<%s>" argument mixes source and test paths. Pass one kind per argument; use the second positional argument for the other kind (e.g. `infection run src/Foo.php tests/FooTest.php`).',
                    $slotName,
                ));
            }
        }

        return $kind;
    }

    /**
     * @return list<non-empty-string>
     */
    private static function resolveAbsoluteSourceDirectories(
        SchemaConfiguration $schema,
        string $configDir,
    ): array {
        $absolute = [];

        foreach ($schema->source->directories as $directory) {
            $resolved = Path::isAbsolute($directory)
                ? $directory
                : Path::join($configDir, $directory);

            $canonical = rtrim(Path::canonicalize($resolved), '/');

            if ($canonical !== '') {
                $absolute[] = $canonical;
            }
        }

        return $absolute;
    }

    /**
     * @param list<non-empty-string> $absoluteSourceDirs
     */
    private static function isInsideSourceDirectories(
        string $path,
        array $absoluteSourceDirs,
    ): bool {
        if ($absoluteSourceDirs === []) {
            return false;
        }

        $candidate = rtrim(Path::canonicalize($path), '/');

        foreach ($absoluteSourceDirs as $sourceDir) {
            if ($candidate === $sourceDir || str_starts_with($candidate, $sourceDir . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<non-empty-string> $absoluteSourceDirs
     *
     * @return 'source'|'test'
     */
    private static function classifyPathKind(
        string $path,
        array $absoluteSourceDirs,
        string $configDir,
        FileSystem $fileSystem,
    ): string {
        // TODO: FQCN-style arguments (e.g. "\App\Foo" or "\App\Foo::method::45") will
        // be supported via https://github.com/infection/infection/issues/2237
        if (self::looksLikeFqcn($path)) {
            throw new InvalidArgumentException(sprintf(
                'FQCN-style arguments like "%s" are not yet supported. See https://github.com/infection/infection/issues/2237.',
                $path,
            ));
        }

        if (self::looksLikeTestPath($path)) {
            return self::KIND_TEST;
        }

        // like `SomeFile` or `SomeFile.php` - bare values behave as --filter values
        if (self::looksLikeClassOrFileName($path)) {
            return self::KIND_SOURCE;
        }

        // at this point, both for `source` and for `test` slots we must have a real file path for provided value
        $absolutePath = Path::isAbsolute($path)
            ? $path
            : Path::join($configDir, $path);

        if (!$fileSystem->isReadableFile($absolutePath) && !$fileSystem->isReadableDirectory($absolutePath)) {
            throw new InvalidArgumentException(sprintf(
                'Positional path "%s" does not exist (resolved to "%s"). Check the path, or pass it via "--%s" / "--%s" explicitly.',
                $path,
                $absolutePath,
                SourceFilterOptions::PLAIN_FILTER_NAME,
                TestFrameworkExtraArgsOption::NAME,
            ));
        }

        if (self::isInsideSourceDirectories($absolutePath, $absoluteSourceDirs)) {
            return self::KIND_SOURCE;
        }

        return self::KIND_TEST;
    }

    /**
     * \SomeNamespace\Class
     * \SomeNamespace\Class::method
     * \SomeNamespace\Class::method::34
     * App\Foo (bare, unbackslashed)
     */
    private static function looksLikeFqcn(string $value): bool
    {
        if (str_starts_with($value, '\\') || str_contains($value, '::')) {
            return true;
        }

        return class_exists($value);
    }

    private static function looksLikeClassOrFileName(string $value): bool
    {
        return strtoupper($value[0]) === $value[0] // class and file name starts with Pascal Case
            && !str_contains($value, '/')
            && !str_contains($value, '\\');
    }

    private static function looksLikeTestPath(string $value): bool
    {
        $normalized = str_replace('\\', '/', $value);

        foreach (['tests', 'test'] as $segment) {
            if ($normalized === $segment || str_starts_with($normalized, $segment . '/')) {
                return true;
            }

            if (str_contains($normalized, '/' . $segment . '/')) {
                return true;
            }
        }

        return str_ends_with(basename($normalized), 'Test.php');
    }
}
