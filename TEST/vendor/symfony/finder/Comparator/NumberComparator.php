<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Finder\Comparator;

class NumberComparator extends Comparator
{
    public function __construct(?string $test)
    {
        if (null === $test || !\preg_match('#^\\s*(==|!=|[<>]=?)?\\s*([0-9\\.]+)\\s*([kmg]i?)?\\s*$#i', $test, $matches)) {
            throw new \InvalidArgumentException(\sprintf('Don\'t understand "%s" as a number test.', $test ?? 'null'));
        }
        $target = $matches[2];
        if (!\is_numeric($target)) {
            throw new \InvalidArgumentException(\sprintf('Invalid number "%s".', $target));
        }
        if (isset($matches[3])) {
            switch (\strtolower($matches[3])) {
                case 'k':
                    $target *= 1000;
                    break;
                case 'ki':
                    $target *= 1024;
                    break;
                case 'm':
                    $target *= 1000000;
                    break;
                case 'mi':
                    $target *= 1024 * 1024;
                    break;
                case 'g':
                    $target *= 1000000000;
                    break;
                case 'gi':
                    $target *= 1024 * 1024 * 1024;
                    break;
            }
        }
        parent::__construct($target, $matches[1] ?: '==');
    }
}
