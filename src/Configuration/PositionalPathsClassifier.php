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
 * Classifies the positional `path` / `path2` slots into source-filter and
 * test-framework-extra-args buckets.
 *
 * Rules enforced:
 *   - Each slot's items are classified against the configured
 *     "source.directories"; all items in a slot must classify as the same kind
 *     (all source, or all test). Mixing source and test paths within one slot
 *     is rejected — use the two separate slots for that.
 *   - When both slots are populated, they must classify as different kinds.
 *     Two source slots (or two test slots) are rejected — combine same-kind
 *     source paths with commas inside a single slot instead.
 *   - Comma-separated lists are supported for SOURCE paths only (mimicking
 *     the "--filter" option). Test slots must contain exactly one path
 *     (a single file or directory) — PHPUnit's filter accepts a single value.
 *
 * @internal
 */
final readonly class PositionalPathsClassifier
{
    private const string KIND_SOURCE = 'source';

    private const string KIND_TEST = 'test';

    public function __construct(
        /** @var list<non-empty-string> */
        public array $sourcePaths,
        /** @var non-empty-string|null */
        public ?string $testPath,
    ) {
    }

    /**
     * @param list<non-empty-string> $slot1
     * @param list<non-empty-string> $slot2
     */
    public static function fromSlots(
        array $slot1,
        array $slot2,
        SchemaConfiguration $schema,
        FileSystem $fileSystem,
    ): self {
        if ($slot1 === [] && $slot2 === []) {
            return new self([], null);
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
                : ' Pass a single test path (file or directory); comma-separated test paths are not supported.';

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

        return new self($sourcePaths, $testPath);
    }

    public function assertNoConflictWithExplicitOptions(
        bool $isSourceFilterProvided,
        bool $isTestFrameworkExtraArgsProvided,
    ): void {
        if ($this->sourcePaths !== [] && $isSourceFilterProvided) {
            throw new InvalidArgumentException(sprintf(
                'Cannot pass source paths as positional arguments together with the "--%s" option. Use either form, not both.',
                SourceFilterOptions::PLAIN_FILTER_NAME,
            ));
        }

        if ($this->testPath !== null && $isTestFrameworkExtraArgsProvided) {
            throw new InvalidArgumentException(sprintf(
                'Cannot pass test paths as positional arguments together with the "--%s" option. Use either form, not both.',
                TestFrameworkExtraArgsOption::NAME,
            ));
        }
    }

    /**
     * Comma-separated lists are a "--filter"-style affordance for source paths
     * only. PHPUnit's filter takes a single path (file or directory), so a test
     * slot containing more than one path is treated as user error.
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
     * Classify every item in the slot. Returns the kind shared by all items, or
     * null when the slot is empty. Throws when items disagree.
     *
     * @param list<non-empty-string> $slot
     * @param list<non-empty-string> $absoluteSourceDirs
     *
     * @return self::KIND_*|null
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
     * @return self::KIND_
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

        if (self::isInsideSourceDirectories($absolutePath, $absoluteSourceDirs, $configDir)) {
            return self::KIND_SOURCE;
        }

        return self::KIND_TEST;
    }

    /**
     * \SomeNamespace\Class
     * \SomeNamespace\Class::method
     * \SomeNamespace\Class::method::34
     */
    private static function looksLikeFqcn(string $value): bool
    {
        if (class_exists($value)) {
            return true;
        }

        return str_starts_with($value, '\\') || str_contains($value, '::');
    }

    private static function looksLikeClassOrFileName(string $value): bool
    {
        return strtoupper($value[0]) === $value[0] // class and file name starts with Pascal Case
            && !str_contains($value, '/')
            && !str_contains($value, '\\');
    }

    /**
     * Recognises the conventional test directory layouts used by PHPUnit
     * projects: "tests/" (Symfony, modern PSR-4) and "test/" (older PSR-0,
     * some Composer setups). Anything past basename(Test.php) suffix is the
     * universal naming convention. Paths that don't match still get a second
     * chance via the source.directories fallback in classifyPathKind.
     */
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
