<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Finder\Comparator;

class DateComparator extends Comparator
{
    public function __construct(string $test)
    {
        if (!\preg_match('#^\\s*(==|!=|[<>]=?|after|since|before|until)?\\s*(.+?)\\s*$#i', $test, $matches)) {
            throw new \InvalidArgumentException(\sprintf('Don\'t understand "%s" as a date test.', $test));
        }
        try {
            $date = new \DateTime($matches[2]);
            $target = $date->format('U');
        } catch (\Exception) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a valid date.', $matches[2]));
        }
        $operator = $matches[1] ?? '==';
        if ('since' === $operator || 'after' === $operator) {
            $operator = '>';
        }
        if ('until' === $operator || 'before' === $operator) {
            $operator = '<';
        }
        parent::__construct($target, $operator);
    }
}
