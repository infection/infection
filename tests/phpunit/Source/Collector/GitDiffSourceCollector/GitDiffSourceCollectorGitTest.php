<?php

namespace Infection\Tests\Source\Collector\GitDiffSourceCollector;

use Infection\Source\Collector\GitDiffSourceCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitDiffSourceCollectorGit::class)]
final class GitDiffSourceCollectorGitTest extends TestCase
{
    /**
     * @param non-empty-string $diffFilter E.g. 'AM'.
     * @param non-empty-string $base E.g. 'origin/main' or a commit hash.
     * @param non-empty-string[] $sourceDirectories
     * @param non-empty-string $changedFileRelativePaths
     */
    #[DataProvider('changedFileRelativePathsProvider')]
    public function test_it_can_get_the_changed_file_relative_paths(
        string $diffFilter,
        string $base,
        array $sourceDirectories,
        string $changedFileRelativePaths,
        string $expected,
    ): void {
        $git = new GitDiffSourceCollectorGit($changedFileRelativePaths);

        $actual = $git->getChangedFileRelativePaths(
            $diffFilter,
            $base,
            $sourceDirectories,
        );

        $this->assertSame($expected, $actual);
    }

    public static function changedFileRelativePathsProvider(): iterable
    {
        yield 'nominal' => [
            'AM',
            'main-hash',
            ['src', 'lib'],
            'src/Mailer.php,lib/MailerInterface.php',
            'f(AM, main-hash, [src, lib]) = src/Mailer.php,lib/MailerInterface.php',
        ];

        yield 'no source' => [
            'AM',
            'main-hash',
            [],
            'src/Mailer.php,lib/MailerInterface.php',
            'f(AM, main-hash, []) = src/Mailer.php,lib/MailerInterface.php',
        ];

        yield 'no output' => [
            'AM',
            'main-hash',
            ['src', 'lib'],
            '',
            'f(AM, main-hash, [src, lib]) = ',
        ];
    }
}
