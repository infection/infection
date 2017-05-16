<?php

declare(strict_types=1);

namespace Infection\Command;

use Infection\EventDispatcher\EventDispatcher;
use Infection\Finder\Locator;
use Infection\Guesser\PhpUnitPathGuesser;
use Infection\Guesser\SourceDirGuesser;
use Infection\Mutant\Generator\MutationsGenerator;
use Infection\Process\Builder\ProcessBuilder;
use Infection\Process\Listener\MutationConsoleLoggerSubscriber;
use Infection\Process\Listener\InitialTestsConsoleLoggerSubscriber;
use Infection\Process\Runner\InitialTestsRunner;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\TestFramework\Config\TestFrameworkConfigLocator;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ConfigureCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writeSection($output, 'Welcome to the Infection config generator');

        $output->writeln([
            '',
            'We did not find configuration file. The following questions will help us to generate it for you.',
            '',
        ]);

        $dirsInCurrentDir = array_filter(glob('*'), 'is_dir');
        $phpUnitPath = $this->getPhpUnitConfigPath($input, $output, $dirsInCurrentDir);

        $sourceDirs = $this->getSourceDirs($input, $output, $dirsInCurrentDir);

        if (empty($sourceDirs)) {
            $output->writeln('A source directory was not provided. Unable to generate "humbug.json.dist".');

            return 1;
        }

        $excludedDirs = $this->getExcludedDirs($input, $output, $dirsInCurrentDir, $sourceDirs);
        $timeout = $this->getTimeout($input, $output);

        var_dump('$phpUnitPath=', $phpUnitPath);
        var_dump('$excludedDirs=', $excludedDirs);
        var_dump('$timeout=', $timeout);

        return 0;
    }

    protected function configure()
    {
        $this
            ->setName('configure')
            ->setDescription('Configure ....')
            ->addOption(
                'test-framework',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the Test framework to use (phpunit, phpspec)',
                'phpunit'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $dirsInCurrentDir
     * @return string|null
     */
    private function getPhpUnitConfigPath(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir)
    {
        // TODO use guesser and don't ask question if "app" folder contains phpunit.xml. Just use it and skip questions
        $testFrameworkConfigLocator = new TestFrameworkConfigLocator('.');
        $testFramework = $input->getOption('test-framework');
        $phpUnitPath = null;

        try {
            $testFrameworkConfigLocator->locate($input->getOption('test-framework'));
        } catch (\Exception $e) {
            $defaultValue = null;

            $questionHelper = new QuestionHelper();

            if (file_exists('composer.json')) {
                $phpUnitPathGuesser = new PhpUnitPathGuesser(
                    json_decode(file_get_contents('composer.json'))
                );
                $defaultValue = $phpUnitPathGuesser->guess();
            }

            $questionText = $this->getQuestion(
                'Where is your <comment>phpunit.xml(.dist)</comment> configuration located?',
                $defaultValue
            );

            $question = new Question($questionText, $defaultValue);
            $question->setAutocompleterValues($dirsInCurrentDir);
            $question->setValidator(function ($answerDir) use ($testFrameworkConfigLocator, $testFramework) {

                $answerDir = trim($answerDir);

                if (!$answerDir) {
                    return $answerDir;
                }

                if (!is_dir($answerDir)) {
                    throw new \RuntimeException(sprintf('Could not find "%s" directory.', $answerDir));
                }

                $testFrameworkConfigLocator->locate($testFramework, $answerDir);

                return $answerDir;
            });

            $phpUnitPath = $questionHelper->ask($input, $output, $question);
        }

        return $phpUnitPath;
    }

    private function getSourceDirs(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir): array
    {
        $output->writeln(['']);

        $guessedSourceDirs = null;
        $questionHelper = new QuestionHelper();

        if (file_exists('composer.json')) {
            $sourceDirGuesser = new SourceDirGuesser(
                json_decode(file_get_contents('composer.json'))
            );
            $guessedSourceDirs = $sourceDirGuesser->guess();
        }

        $questionText = $this->getQuestion(
            'What source directories do you want to include (comma separated)?',
            $guessedSourceDirs ? implode(',', $guessedSourceDirs) : null
        );

        $choices = array_merge(['.'], array_values($dirsInCurrentDir));
        $defaultValues = $guessedSourceDirs ? implode(',', $guessedSourceDirs) : null;

        $question = new ChoiceQuestion($questionText, $choices, $defaultValues);
        $question->setMultiselect(true);

        $sourceFolders = $questionHelper->ask($input, $output, $question);

        // TODO issue with "." https://github.com/symfony/symfony/issues/22706
        // TODO ^ possible to solve with $timeoutQuestion->getValidator() and compose (try/catche?)

        // TODO [src,app]: - why do I suggest app folder as a default?

        if (in_array('.', $sourceFolders, true) && count($sourceFolders) > 1) {
            throw new \LogicException('You cannot use current folder "." with other subfolders');
        }

        return $sourceFolders;
    }

    private function getExcludedDirs(InputInterface $input, OutputInterface $output, array $dirsInCurrentDir, array $sourceDirs): array
    {
        $output->writeln([
            '',
            'There can be situations when you want to exclude some folders from generating mutants.',
            'You can use glob pattern (<comment>*Bundle/**/*/Tests)</comment> for them or just regular dir names.',
            'Press <comment><return></comment> to stop/skip adding dirs.',
            '',
        ]);

        $autocompleteValues = [];
        $questionText = $this->getQuestion(
            'Any directories to exclude from within your source directories?',
            ''
        );
        $questionHelper = new QuestionHelper();

        $excludedDirs = [];

        if ($sourceDirs === ['.']) {
            if (is_dir('vendor')) {
                $excludedDirs[] = 'vendor';
            }

            $autocompleteValues = $dirsInCurrentDir;
        } elseif (count($sourceDirs) === 1) {
            // TODO type src/Command
             $globDirs = array_filter(glob($sourceDirs[0] .'/*'), 'is_dir');

             $autocompleteValues = array_map(
                function (string $dir) use ($sourceDirs) {
                    return str_replace($sourceDirs[0] . '/', '', $dir);
                },
                 $globDirs
             );
        }

        $locator = new Locator($sourceDirs);

        $question = new Question($questionText, '');
        $question->setAutocompleterValues($autocompleteValues);

        // TODO add the same for sourceDir
        $question->setValidator(function ($answer) use ($locator) {
            if (!$answer || strpos($answer, '*') !== false) {
                return $answer;
            }

            $locator->locate($answer);

            return $answer;
        });

        while ($dir = $questionHelper->ask($input, $output, $question)) {
            if ($dir) {
                $excludedDirs[] = $dir;
            }
        }

        return array_unique($excludedDirs);
    }

    private function getTimeout(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['']);
        $questionHelper = new QuestionHelper();
        $defaultValue = 10;

        $questionText = $this->getQuestion('Single test suite timeout in seconds', $defaultValue);

        $timeoutQuestion = new Question($questionText, $defaultValue);
        $timeoutQuestion->setValidator(function ($answer) {
            if (!$answer || !is_numeric($answer) || (int) $answer <= 0) {
                throw new \RuntimeException('Timeout should be an integer greater than 0');
            }

            return (int)$answer;
        });


        return $questionHelper->ask($input, $output, $timeoutQuestion);
    }

    private function getQuestionHelper()
    {
        return $this->getHelper('question');
    }

    public function writeSection(OutputInterface $output, $text, $style = 'bg=blue;fg=white')
    {
        $output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, $style, true),
            '',
        ));
    }

    public function getQuestion($question, $default, $sep = ':')
    {
        return $default ? sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $sep) : sprintf('<info>%s</info>%s ', $question, $sep);
    }
}