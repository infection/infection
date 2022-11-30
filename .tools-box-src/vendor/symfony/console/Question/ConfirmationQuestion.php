<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Question;

class ConfirmationQuestion extends Question
{
    private string $trueAnswerRegex;
    public function __construct(string $question, bool $default = \true, string $trueAnswerRegex = '/^y/i')
    {
        parent::__construct($question, $default);
        $this->trueAnswerRegex = $trueAnswerRegex;
        $this->setNormalizer($this->getDefaultNormalizer());
    }
    private function getDefaultNormalizer() : callable
    {
        $default = $this->getDefault();
        $regex = $this->trueAnswerRegex;
        return function ($answer) use($default, $regex) {
            if (\is_bool($answer)) {
                return $answer;
            }
            $answerIsTrue = (bool) \preg_match($regex, $answer);
            if (\false === $default) {
                return $answer && $answerIsTrue;
            }
            return '' === $answer || $answerIsTrue;
        };
    }
}
