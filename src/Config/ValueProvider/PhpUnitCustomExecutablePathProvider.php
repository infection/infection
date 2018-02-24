<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Config\ValueProvider;

use Infection\Config\ConsoleHelper;
use Infection\Finder\Exception\FinderException;
use Infection\Finder\TestFrameworkFinder;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class PhpUnitCustomExecutablePathProvider
{
    /**
     * @var TestFrameworkFinder
     */
    private $phpUnitExecutableFinder;
    /**
     * @var ConsoleHelper
     */
    private $consoleHelper;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    public function __construct(TestFrameworkFinder $phpUnitExecutableFinder, ConsoleHelper $consoleHelper, QuestionHelper $questionHelper)
    {
        $this->phpUnitExecutableFinder = $phpUnitExecutableFinder;
        $this->consoleHelper = $consoleHelper;
        $this->questionHelper = $questionHelper;
    }

    public function get(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->phpUnitExecutableFinder->find();
        } catch (FinderException $e) {
            $output->writeln(['']);

            $questionText = $this->consoleHelper->getQuestion(
                'We did not find phpunit executable. Please provide custom absolute path'
            );

            $question = new Question($questionText);
            $question->setValidator($this->getValidator());

            return str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                $this->questionHelper->ask($input, $output, $question)
            );
        }

        return null;
    }

    private function getValidator(): \Closure
    {
        return function ($answerPath) {
            $answerPath = $answerPath ? trim($answerPath) : $answerPath;

            if (!$answerPath || !file_exists($answerPath)) {
                throw new \RuntimeException(sprintf('Custom path "%s" is incorrect.', $answerPath));
            }

            return $answerPath;
        };
    }
}
