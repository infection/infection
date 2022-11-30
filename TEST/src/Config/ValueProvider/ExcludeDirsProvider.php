<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Config\ValueProvider;

use function array_map;
use function array_unique;
use function array_values;
use Closure;
use function count;
use const GLOB_ONLYDIR;
use function in_array;
use _HumbugBox9658796bb9f0\Infection\Config\ConsoleHelper;
use _HumbugBox9658796bb9f0\Infection\Console\IO;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Locator\Locator;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Locator\RootsFileOrDirectoryLocator;
use function _HumbugBox9658796bb9f0\Safe\glob;
use function str_replace;
use function strpos;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\QuestionHelper;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Question\Question;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
final class ExcludeDirsProvider
{
    public const EXCLUDED_ROOT_DIRS = ['vendor', 'tests', 'test'];
    private ConsoleHelper $consoleHelper;
    private QuestionHelper $questionHelper;
    private Filesystem $filesystem;
    public function __construct(ConsoleHelper $consoleHelper, QuestionHelper $questionHelper, Filesystem $filesystem)
    {
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
        $this->filesystem = $filesystem;
    }
    public function get(IO $io, array $dirsInCurrentDir, array $sourceDirs) : array
    {
        $io->writeln(['', 'There can be situations when you want to exclude some folders from generating mutants.', 'You can use glob pattern (<comment>*Bundle/**/*/Tests</comment>) for them or just regular dir path.', 'It should be <comment>relative</comment> to the source directory.', '<comment>You should not mutate test suite files.</comment>', 'Press <comment><return></comment> to stop/skip adding dirs.', '']);
        $autocompleteValues = [];
        $questionText = $this->consoleHelper->getQuestion('Any directories to exclude from within your source directories?', '');
        $excludedDirs = [];
        if ($sourceDirs === ['.']) {
            foreach (self::EXCLUDED_ROOT_DIRS as $dir) {
                if (in_array($dir, $dirsInCurrentDir, \true)) {
                    $excludedDirs[] = $dir;
                }
            }
            $autocompleteValues = $dirsInCurrentDir;
        } elseif (count($sourceDirs) === 1) {
            $globDirs = glob($sourceDirs[0] . '/*', GLOB_ONLYDIR);
            $autocompleteValues = array_map(static function (string $dir) use($sourceDirs) : string {
                return str_replace($sourceDirs[0] . '/', '', $dir);
            }, $globDirs);
        }
        $question = new Question($questionText, '');
        $question->setAutocompleterValues($autocompleteValues);
        $question->setValidator($this->getValidator(new RootsFileOrDirectoryLocator($sourceDirs, $this->filesystem)));
        while ($dir = $this->questionHelper->ask($io->getInput(), $io->getOutput(), $question)) {
            $excludedDirs[] = $dir;
        }
        return array_values(array_unique($excludedDirs));
    }
    private function getValidator(Locator $locator) : Closure
    {
        return static function ($answer) use($locator) {
            if (!$answer || strpos($answer, '*') !== \false) {
                return $answer;
            }
            $locator->locate($answer);
            return $answer;
        };
    }
}
