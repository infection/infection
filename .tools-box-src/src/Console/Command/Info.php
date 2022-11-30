<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console\Command;

use function array_filter;
use function array_flip;
use DirectoryIterator;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Command;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Configuration;
use _HumbugBoxb47773b41c19\Fidry\Console\ExitCode;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use function implode;
use function is_array;
use _HumbugBoxb47773b41c19\KevinGH\Box\Console\PharInfoRenderer;
use function _HumbugBoxb47773b41c19\KevinGH\Box\create_temporary_phar;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\remove;
use function _HumbugBoxb47773b41c19\KevinGH\Box\format_size;
use function _HumbugBoxb47773b41c19\KevinGH\Box\get_phar_compression_algorithms;
use _HumbugBoxb47773b41c19\KevinGH\Box\PharInfo\PharInfo;
use Phar;
use PharData;
use PharFileInfo;
use function realpath;
use function sprintf;
use function str_repeat;
use function str_replace;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputArgument;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Path;
use Throwable;
final class Info implements Command
{
    private const PHAR_ARG = 'phar';
    private const LIST_OPT = 'list';
    private const METADATA_OPT = 'metadata';
    private const MODE_OPT = 'mode';
    private const DEPTH_OPT = 'depth';
    private const MODES = ['indent', 'flat'];
    private static array $FILE_ALGORITHMS;
    public function __construct()
    {
        if (!isset(self::$FILE_ALGORITHMS)) {
            self::$FILE_ALGORITHMS = array_flip(array_filter(get_phar_compression_algorithms()));
        }
    }
    public function getConfiguration() : Configuration
    {
        return new Configuration('info', '🔍  Displays information about the PHAR extension or file', <<<'HELP'
The <info>%command.name%</info> command will display information about the Phar extension,
or the Phar file if specified.

If the <info>phar</info> argument <comment>(the PHAR file path)</comment> is provided, information
about the PHAR file itself will be displayed.

If the <info>--list|-l</info> option is used, the contents of the PHAR file will
be listed. By default, the list is shown as an indented tree. You may
instead choose to view a flat listing, by setting the <info>--mode|-m</info> option
to <comment>flat</comment>.
HELP
, [new InputArgument(self::PHAR_ARG, InputArgument::OPTIONAL, 'The Phar file.')], [new InputOption(self::LIST_OPT, 'l', InputOption::VALUE_NONE, 'List the contents of the Phar?'), new InputOption(self::METADATA_OPT, null, InputOption::VALUE_NONE, 'Display metadata?'), new InputOption(self::MODE_OPT, 'm', InputOption::VALUE_REQUIRED, sprintf('The listing mode. Modes available: "%s"', implode('", "', self::MODES)), 'indent'), new InputOption(self::DEPTH_OPT, 'd', InputOption::VALUE_REQUIRED, 'The depth of the tree displayed', '-1')]);
    }
    public function execute(IO $io) : int
    {
        $io->newLine();
        $file = $io->getArgument(self::PHAR_ARG)->asNullableNonEmptyString();
        if (null === $file) {
            return self::showGlobalInfo($io);
        }
        $file = Path::canonicalize($file);
        $fileRealPath = realpath($file);
        if (\false === $fileRealPath) {
            $io->error(sprintf('The file "%s" could not be found.', $file));
            return ExitCode::FAILURE;
        }
        $tmpFile = create_temporary_phar($fileRealPath);
        try {
            return self::showInfo($tmpFile, $fileRealPath, $io);
        } finally {
            remove($tmpFile);
        }
    }
    public static function showInfo(string $file, string $originalFile, IO $io) : int
    {
        $maxDepth = self::getMaxDepth($io);
        $mode = $io->getOption(self::MODE_OPT)->asStringChoice(self::MODES);
        try {
            $pharInfo = new PharInfo($file);
            return self::showPharInfo($pharInfo, $io->getOption(self::LIST_OPT)->asBoolean(), $maxDepth, 'indent' === $mode, $io);
        } catch (Throwable $throwable) {
            if ($io->isDebug()) {
                throw $throwable;
            }
            $io->error(sprintf('Could not read the file "%s".', $originalFile));
            return ExitCode::FAILURE;
        }
    }
    private static function getMaxDepth(IO $io) : int
    {
        $option = $io->getOption(self::DEPTH_OPT);
        return '-1' === $option->asRaw() ? -1 : $option->asNatural(sprintf('Expected the depth to be a positive integer or -1: "%s".', $option->asRaw()));
    }
    private static function showGlobalInfo(IO $io) : int
    {
        self::render($io, ['API Version' => Phar::apiVersion(), 'Supported Compression' => Phar::getSupportedCompression(), 'Supported Signatures' => Phar::getSupportedSignatures()]);
        $io->newLine();
        $io->comment('Get a PHAR details by giving its path as an argument.');
        return ExitCode::SUCCESS;
    }
    private static function showPharInfo(PharInfo $pharInfo, bool $content, int $depth, bool $indent, IO $io) : int
    {
        self::showPharMeta($pharInfo, $io);
        if ($content) {
            self::renderContents($io, $pharInfo->getPhar(), 0, $depth, $indent ? 0 : \false, $pharInfo->getRoot(), $pharInfo->getPhar(), $pharInfo->getRoot());
        } else {
            $io->comment('Use the <info>--list|-l</info> option to list the content of the PHAR.');
        }
        return ExitCode::SUCCESS;
    }
    private static function showPharMeta(PharInfo $pharInfo, IO $io) : void
    {
        $io->writeln(sprintf('<comment>API Version:</comment> %s', $pharInfo->getVersion()));
        $io->newLine();
        PharInfoRenderer::renderCompression($pharInfo, $io);
        $io->newLine();
        PharInfoRenderer::renderSignature($pharInfo, $io);
        $io->newLine();
        PharInfoRenderer::renderMetadata($pharInfo, $io);
        $io->newLine();
        PharInfoRenderer::renderContentsSummary($pharInfo, $io);
    }
    private static function render(IO $io, array $attributes) : void
    {
        $out = \false;
        foreach ($attributes as $name => $value) {
            if ($out) {
                $io->writeln('');
            }
            $io->write("<comment>{$name}:</comment>");
            if (is_array($value)) {
                $io->writeln('');
                foreach ($value as $v) {
                    $io->writeln("  - {$v}");
                }
            } else {
                $io->writeln(" {$value}");
            }
            $out = \true;
        }
    }
    private static function renderContents(OutputInterface $output, iterable $list, int $depth, int $maxDepth, int|false $indent, string $base, Phar|PharData $phar, string $root) : void
    {
        if (-1 !== $maxDepth && $depth > $maxDepth) {
            return;
        }
        foreach ($list as $item) {
            $item = $phar[str_replace($root, '', $item->getPathname())];
            if (\false !== $indent) {
                $output->write(str_repeat(' ', $indent));
                $path = $item->getFilename();
                if ($item->isDir()) {
                    $path .= '/';
                }
            } else {
                $path = str_replace($base, '', $item->getPathname());
            }
            if ($item->isDir()) {
                if (\false !== $indent) {
                    $output->writeln("<info>{$path}</info>");
                }
            } else {
                $compression = '<fg=red>[NONE]</fg=red>';
                foreach (self::$FILE_ALGORITHMS as $code => $name) {
                    if ($item->isCompressed($code)) {
                        $compression = "<fg=cyan>[{$name}]</fg=cyan>";
                        break;
                    }
                }
                $fileSize = format_size($item->getCompressedSize());
                $output->writeln(sprintf('%s %s - %s', $path, $compression, $fileSize));
            }
            if ($item->isDir()) {
                self::renderContents($output, new DirectoryIterator($item->getPathname()), $depth + 1, $maxDepth, \false === $indent ? $indent : $indent + 2, $base, $phar, $root);
            }
        }
    }
}
