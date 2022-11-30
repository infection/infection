<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\InvalidArgumentException;
class TableCell
{
    private string $value;
    private array $options = ['rowspan' => 1, 'colspan' => 1, 'style' => null];
    public function __construct(string $value = '', array $options = [])
    {
        $this->value = $value;
        if ($diff = \array_diff(\array_keys($options), \array_keys($this->options))) {
            throw new InvalidArgumentException(\sprintf('The TableCell does not support the following options: \'%s\'.', \implode('\', \'', $diff)));
        }
        if (isset($options['style']) && !$options['style'] instanceof TableCellStyle) {
            throw new InvalidArgumentException('The style option must be an instance of "TableCellStyle".');
        }
        $this->options = \array_merge($this->options, $options);
    }
    public function __toString() : string
    {
        return $this->value;
    }
    public function getColspan() : int
    {
        return (int) $this->options['colspan'];
    }
    public function getRowspan() : int
    {
        return (int) $this->options['rowspan'];
    }
    public function getStyle() : ?TableCellStyle
    {
        return $this->options['style'];
    }
}
