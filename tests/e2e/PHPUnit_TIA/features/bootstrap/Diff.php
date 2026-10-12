<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use Webmozart\Assert\Assert;
use function array_filter;
use function array_map;
use function explode;
use function implode;
use function sprintf;
use function substr;

/**
 * The original and replacement contents of a single diff hunk without file or line-number headers.
 */
final readonly class Diff
{
    private const string REMOVED_LINE_MARKER = '-';
    private const string ADDED_LINE_MARKER = '+';
    private const string UNCHANGED_LINE_MARKER = ' ';

    private function __construct(
        public string $original,
        public string $replacement,
    ) {
    }

    public static function fromString(string $diff): self
    {
        $lines = explode("\n", $diff);

        self::assertValidLineMarkers($lines);

        $originalLines = self::excludeLinesWithMarker($lines, self::ADDED_LINE_MARKER);
        $replacementLines = self::excludeLinesWithMarker($lines, self::REMOVED_LINE_MARKER);

        Assert::notEmpty($originalLines, 'The diff must include original lines or context to identify its location.');

        return new self(
            original: self::buildContents($originalLines),
            replacement: self::buildContents($replacementLines),
        );
    }

    /**
     * @param list<string> $lines
     */
    private static function assertValidLineMarkers(array $lines): void
    {
        Assert::allInArray(
            array_map(
                static fn (string $line): string => $line[0] ?? '',
                $lines,
            ),
            [
                self::UNCHANGED_LINE_MARKER,
                self::REMOVED_LINE_MARKER,
                self::ADDED_LINE_MARKER,
            ],
            sprintf(
                'Each diff line must start with a space, %s or %s.',
                self::REMOVED_LINE_MARKER,
                self::ADDED_LINE_MARKER,
            ),
        );
    }

    /**
     * @param list<string> $lines
     *
     * @return string[]
     */
    private static function excludeLinesWithMarker(array $lines, string $excludedMarker): array
    {
        return array_filter(
            $lines,
            static fn (string $line): bool => $line[0] !== $excludedMarker,
        );
    }

    /**
     * @param string[] $lines
     */
    private static function buildContents(array $lines): string
    {
        return implode(
            '',
            array_map(
                self::toContentLine(...),
                $lines,
            ),
        );
    }

    private static function toContentLine(string $line): string
    {
        return substr($line, 1) . "\n";
    }
}
