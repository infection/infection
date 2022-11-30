<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Helper;

use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\RuntimeException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\QuestionHelper as SymfonyQuestionHelper;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Question\Question;
final class QuestionHelper
{
    private SymfonyQuestionHelper $helper;
    public function __construct()
    {
        $this->helper = new SymfonyQuestionHelper();
    }
    public function ask(IO $io, Question $question)
    {
        return $this->helper->ask($io->getInput(), $io->getOutput(), $question);
    }
}
