<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Contracts\Service\ResetInterface;
class OutputFormatterStyleStack implements ResetInterface
{
    private $styles;
    private $emptyStyle;
    public function __construct(OutputFormatterStyleInterface $emptyStyle = null)
    {
        $this->emptyStyle = $emptyStyle ?? new OutputFormatterStyle();
        $this->reset();
    }
    public function reset()
    {
        $this->styles = [];
    }
    public function push(OutputFormatterStyleInterface $style)
    {
        $this->styles[] = $style;
    }
    public function pop(OutputFormatterStyleInterface $style = null)
    {
        if (empty($this->styles)) {
            return $this->emptyStyle;
        }
        if (null === $style) {
            return \array_pop($this->styles);
        }
        foreach (\array_reverse($this->styles, \true) as $index => $stackedStyle) {
            if ($style->apply('') === $stackedStyle->apply('')) {
                $this->styles = \array_slice($this->styles, 0, $index);
                return $stackedStyle;
            }
        }
        throw new InvalidArgumentException('Incorrectly nested style tag found.');
    }
    public function getCurrent()
    {
        if (empty($this->styles)) {
            return $this->emptyStyle;
        }
        return $this->styles[\count($this->styles) - 1];
    }
    public function setEmptyStyle(OutputFormatterStyleInterface $emptyStyle)
    {
        $this->emptyStyle = $emptyStyle;
        return $this;
    }
    public function getEmptyStyle()
    {
        return $this->emptyStyle;
    }
}
