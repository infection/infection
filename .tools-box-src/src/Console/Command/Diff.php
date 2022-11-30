<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console\Command;

use function array_filter;
use function array_flip;
use function array_map;
use function count;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Command;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Configuration;
use _HumbugBoxb47773b41c19\Fidry\Console\ExitCode;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use function is_string;
use function _HumbugBoxb47773b41c19\KevinGH\Box\check_php_settings;
use _HumbugBoxb47773b41c19\KevinGH\Box\Console\PharInfoRenderer;
use function _HumbugBoxb47773b41c19\KevinGH\Box\format_size;
use function _HumbugBoxb47773b41c19\KevinGH\Box\get_phar_compression_algorithms;
use _HumbugBoxb47773b41c19\KevinGH\Box\PharInfo\PharDiff;
use _HumbugBoxb47773b41c19\KevinGH\Box\PharInfo\PharInfo;
use PharFileInfo;
use function sprintf;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputArgument;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Path;
use Throwable;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class Diff implements Command
{
    private const FIRST_PHAR_ARG = 'pharA';
    private const SECOND_PHAR_ARG = 'pharB';
    private const LIST_FILES_DIFF_OPTION = 'list-diff';
    private const GIT_DIFF_OPTION = 'git-diff';
    private const GNU_DIFF_OPTION = 'gnu-diff';
    private const CHECK_OPTION = 'check';
    private const DEFAULT_CHECKSUM_ALGO = 'sha384';
    private static array $FILE_ALGORITHMS;
    public function __construct()
    {
        if (!isset(self::$FILE_ALGORITHMS)) {
            self::$FILE_ALGORITHMS = array_flip(array_filter(get_phar_compression_algorithms()));
        }
    }
    public function getConfiguration() : Configuration
    {
        return new Configuration('diff', '🕵  Displays the differences between all of the files in two PHARs', '', [new InputArgument(self::FIRST_PHAR_ARG, InputArgument::REQUIRED, 'The first PHAR'), new InputArgument(self::SECOND_PHAR_ARG, InputArgument::REQUIRED, 'The second PHAR')], [new InputOption(self::GNU_DIFF_OPTION, null, InputOption::VALUE_NONE, 'Displays a GNU diff'), new InputOption(self::GIT_DIFF_OPTION, null, InputOption::VALUE_NONE, 'Displays a Git diff'), new InputOption(self::LIST_FILES_DIFF_OPTION, null, InputOption::VALUE_NONE, 'Displays a list of file names diff (default)'), new InputOption(self::CHECK_OPTION, 'c', InputOption::VALUE_OPTIONAL, 'Verify the authenticity of the contents between the two PHARs with the given hash function', self::DEFAULT_CHECKSUM_ALGO)]);
    }
    public function execute(IO $io) : int
    {
        check_php_settings($io);
        $paths = self::getPaths($io);
        try {
            $diff = new PharDiff(...$paths);
        } catch (Throwable $throwable) {
            if ($io->isDebug()) {
                throw $throwable;
            }
            $io->writeln(sprintf('<error>Could not check the PHARs: %s</error>', $throwable->getMessage()));
            return ExitCode::FAILURE;
        }
        $result1 = $this->compareArchives($diff, $io);
        $result2 = $this->compareContents($diff, $io);
        return $result1 + $result2;
    }
    private static function getPaths(IO $io) : array
    {
        $paths = [$io->getArgument(self::FIRST_PHAR_ARG)->asNonEmptyString(), $io->getArgument(self::SECOND_PHAR_ARG)->asNonEmptyString()];
        Assert::allFile($paths);
        return array_map(static fn(string $path) => Path::canonicalize($path), $paths);
    }
    private function compareArchives(PharDiff $diff, IO $io) : int
    {
        $io->comment('<info>Comparing the two archives... (do not check the signatures)</info>');
        $pharInfoA = $diff->getPharA()->getPharInfo();
        $pharInfoB = $diff->getPharB()->getPharInfo();
        if ($pharInfoA->equals($pharInfoB)) {
            $io->success('The two archives are identical');
            return ExitCode::SUCCESS;
        }
        self::renderArchive($diff->getPharA()->getFileName(), $pharInfoA, $io);
        $io->newLine();
        self::renderArchive($diff->getPharB()->getFileName(), $pharInfoB, $io);
        return ExitCode::FAILURE;
    }
    private function compareContents(PharDiff $diff, IO $io) : int
    {
        $io->comment('<info>Comparing the two archives contents...</info>');
        $checkSumAlgorithm = $io->getOption(self::CHECK_OPTION)->asNullableNonEmptyString() ?? self::DEFAULT_CHECKSUM_ALGO;
        if ($io->hasOption('-c') || $io->hasOption('--check')) {
            return $diff->listChecksums($checkSumAlgorithm);
        }
        if ($io->getOption(self::GNU_DIFF_OPTION)->asBoolean()) {
            $diffResult = $diff->gnuDiff();
        } elseif ($io->getOption(self::GIT_DIFF_OPTION)->asBoolean()) {
            $diffResult = $diff->gitDiff();
        } else {
            $diffResult = $diff->listDiff();
        }
        if (null === $diffResult || [[], []] === $diffResult) {
            $io->success('The contents are identical');
            return ExitCode::SUCCESS;
        }
        if (is_string($diffResult)) {
            $io->writeln($diffResult);
            return ExitCode::FAILURE;
        }
        $io->writeln(sprintf('--- Files present in "%s" but not in "%s"', $diff->getPharA()->getFileName(), $diff->getPharB()->getFileName()));
        $io->writeln(sprintf('+++ Files present in "%s" but not in "%s"', $diff->getPharB()->getFileName(), $diff->getPharA()->getFileName()));
        $io->newLine();
        self::renderPaths('-', $diff->getPharA()->getPharInfo(), $diffResult[0], $io);
        self::renderPaths('+', $diff->getPharB()->getPharInfo(), $diffResult[1], $io);
        $io->error(sprintf('%d file(s) difference', count($diffResult[0]) + count($diffResult[1])));
        return ExitCode::FAILURE;
    }
    private static function renderPaths(string $symbol, PharInfo $pharInfo, array $paths, IO $io) : void
    {
        foreach ($paths as $path) {
            $file = $pharInfo->getPhar()[\str_replace($pharInfo->getRoot(), '', $path)];
            $compression = '<fg=red>[NONE]</fg=red>';
            foreach (self::$FILE_ALGORITHMS as $code => $name) {
                if ($file->isCompressed($code)) {
                    $compression = "<fg=cyan>[{$name}]</fg=cyan>";
                    break;
                }
            }
            $fileSize = format_size($file->getCompressedSize());
            $io->writeln(sprintf('%s %s %s - %s', $symbol, $path, $compression, $fileSize));
        }
    }
    private static function renderArchive(string $fileName, PharInfo $pharInfo, IO $io) : void
    {
        $io->writeln(sprintf('<comment>Archive: </comment><fg=cyan;options=bold>%s</>', $fileName));
        PharInfoRenderer::renderCompression($pharInfo, $io);
        PharInfoRenderer::renderMetadata($pharInfo, $io);
        PharInfoRenderer::renderContentsSummary($pharInfo, $io);
    }
}
