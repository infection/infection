<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\RuntimeException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatter;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\WrappableOutputFormatterInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\ConsoleSectionOutput;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
class Table
{
    private const SEPARATOR_TOP = 0;
    private const SEPARATOR_TOP_BOTTOM = 1;
    private const SEPARATOR_MID = 2;
    private const SEPARATOR_BOTTOM = 3;
    private const BORDER_OUTSIDE = 0;
    private const BORDER_INSIDE = 1;
    private $headerTitle;
    private $footerTitle;
    private $headers = [];
    private $rows = [];
    private $horizontal = \false;
    private $effectiveColumnWidths = [];
    private $numberOfColumns;
    private $output;
    private $style;
    private $columnStyles = [];
    private $columnWidths = [];
    private $columnMaxWidths = [];
    private static $styles;
    private $rendered = \false;
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        if (!self::$styles) {
            self::$styles = self::initStyles();
        }
        $this->setStyle('default');
    }
    public static function setStyleDefinition(string $name, TableStyle $style)
    {
        if (!self::$styles) {
            self::$styles = self::initStyles();
        }
        self::$styles[$name] = $style;
    }
    public static function getStyleDefinition(string $name)
    {
        if (!self::$styles) {
            self::$styles = self::initStyles();
        }
        if (isset(self::$styles[$name])) {
            return self::$styles[$name];
        }
        throw new InvalidArgumentException(\sprintf('Style "%s" is not defined.', $name));
    }
    public function setStyle($name)
    {
        $this->style = $this->resolveStyle($name);
        return $this;
    }
    public function getStyle()
    {
        return $this->style;
    }
    public function setColumnStyle(int $columnIndex, $name)
    {
        $this->columnStyles[$columnIndex] = $this->resolveStyle($name);
        return $this;
    }
    public function getColumnStyle(int $columnIndex)
    {
        return $this->columnStyles[$columnIndex] ?? $this->getStyle();
    }
    public function setColumnWidth(int $columnIndex, int $width)
    {
        $this->columnWidths[$columnIndex] = $width;
        return $this;
    }
    public function setColumnWidths(array $widths)
    {
        $this->columnWidths = [];
        foreach ($widths as $index => $width) {
            $this->setColumnWidth($index, $width);
        }
        return $this;
    }
    public function setColumnMaxWidth(int $columnIndex, int $width) : self
    {
        if (!$this->output->getFormatter() instanceof WrappableOutputFormatterInterface) {
            throw new \LogicException(\sprintf('Setting a maximum column width is only supported when using a "%s" formatter, got "%s".', WrappableOutputFormatterInterface::class, \get_debug_type($this->output->getFormatter())));
        }
        $this->columnMaxWidths[$columnIndex] = $width;
        return $this;
    }
    public function setHeaders(array $headers)
    {
        $headers = \array_values($headers);
        if (!empty($headers) && !\is_array($headers[0])) {
            $headers = [$headers];
        }
        $this->headers = $headers;
        return $this;
    }
    public function setRows(array $rows)
    {
        $this->rows = [];
        return $this->addRows($rows);
    }
    public function addRows(array $rows)
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
        return $this;
    }
    public function addRow($row)
    {
        if ($row instanceof TableSeparator) {
            $this->rows[] = $row;
            return $this;
        }
        if (!\is_array($row)) {
            throw new InvalidArgumentException('A row must be an array or a TableSeparator instance.');
        }
        $this->rows[] = \array_values($row);
        return $this;
    }
    public function appendRow($row) : self
    {
        if (!$this->output instanceof ConsoleSectionOutput) {
            throw new RuntimeException(\sprintf('Output should be an instance of "%s" when calling "%s".', ConsoleSectionOutput::class, __METHOD__));
        }
        if ($this->rendered) {
            $this->output->clear($this->calculateRowCount());
        }
        $this->addRow($row);
        $this->render();
        return $this;
    }
    public function setRow($column, array $row)
    {
        $this->rows[$column] = $row;
        return $this;
    }
    public function setHeaderTitle(?string $title) : self
    {
        $this->headerTitle = $title;
        return $this;
    }
    public function setFooterTitle(?string $title) : self
    {
        $this->footerTitle = $title;
        return $this;
    }
    public function setHorizontal(bool $horizontal = \true) : self
    {
        $this->horizontal = $horizontal;
        return $this;
    }
    public function render()
    {
        $divider = new TableSeparator();
        if ($this->horizontal) {
            $rows = [];
            foreach ($this->headers[0] ?? [] as $i => $header) {
                $rows[$i] = [$header];
                foreach ($this->rows as $row) {
                    if ($row instanceof TableSeparator) {
                        continue;
                    }
                    if (isset($row[$i])) {
                        $rows[$i][] = $row[$i];
                    } elseif ($rows[$i][0] instanceof TableCell && $rows[$i][0]->getColspan() >= 2) {
                    } else {
                        $rows[$i][] = null;
                    }
                }
            }
        } else {
            $rows = \array_merge($this->headers, [$divider], $this->rows);
        }
        $this->calculateNumberOfColumns($rows);
        $rowGroups = $this->buildTableRows($rows);
        $this->calculateColumnsWidth($rowGroups);
        $isHeader = !$this->horizontal;
        $isFirstRow = $this->horizontal;
        $hasTitle = (bool) $this->headerTitle;
        foreach ($rowGroups as $rowGroup) {
            $isHeaderSeparatorRendered = \false;
            foreach ($rowGroup as $row) {
                if ($divider === $row) {
                    $isHeader = \false;
                    $isFirstRow = \true;
                    continue;
                }
                if ($row instanceof TableSeparator) {
                    $this->renderRowSeparator();
                    continue;
                }
                if (!$row) {
                    continue;
                }
                if ($isHeader && !$isHeaderSeparatorRendered) {
                    $this->renderRowSeparator($isHeader ? self::SEPARATOR_TOP : self::SEPARATOR_TOP_BOTTOM, $hasTitle ? $this->headerTitle : null, $hasTitle ? $this->style->getHeaderTitleFormat() : null);
                    $hasTitle = \false;
                    $isHeaderSeparatorRendered = \true;
                }
                if ($isFirstRow) {
                    $this->renderRowSeparator($isHeader ? self::SEPARATOR_TOP : self::SEPARATOR_TOP_BOTTOM, $hasTitle ? $this->headerTitle : null, $hasTitle ? $this->style->getHeaderTitleFormat() : null);
                    $isFirstRow = \false;
                    $hasTitle = \false;
                }
                if ($this->horizontal) {
                    $this->renderRow($row, $this->style->getCellRowFormat(), $this->style->getCellHeaderFormat());
                } else {
                    $this->renderRow($row, $isHeader ? $this->style->getCellHeaderFormat() : $this->style->getCellRowFormat());
                }
            }
        }
        $this->renderRowSeparator(self::SEPARATOR_BOTTOM, $this->footerTitle, $this->style->getFooterTitleFormat());
        $this->cleanup();
        $this->rendered = \true;
    }
    private function renderRowSeparator(int $type = self::SEPARATOR_MID, string $title = null, string $titleFormat = null)
    {
        if (0 === ($count = $this->numberOfColumns)) {
            return;
        }
        $borders = $this->style->getBorderChars();
        if (!$borders[0] && !$borders[2] && !$this->style->getCrossingChar()) {
            return;
        }
        $crossings = $this->style->getCrossingChars();
        if (self::SEPARATOR_MID === $type) {
            [$horizontal, $leftChar, $midChar, $rightChar] = [$borders[2], $crossings[8], $crossings[0], $crossings[4]];
        } elseif (self::SEPARATOR_TOP === $type) {
            [$horizontal, $leftChar, $midChar, $rightChar] = [$borders[0], $crossings[1], $crossings[2], $crossings[3]];
        } elseif (self::SEPARATOR_TOP_BOTTOM === $type) {
            [$horizontal, $leftChar, $midChar, $rightChar] = [$borders[0], $crossings[9], $crossings[10], $crossings[11]];
        } else {
            [$horizontal, $leftChar, $midChar, $rightChar] = [$borders[0], $crossings[7], $crossings[6], $crossings[5]];
        }
        $markup = $leftChar;
        for ($column = 0; $column < $count; ++$column) {
            $markup .= \str_repeat($horizontal, $this->effectiveColumnWidths[$column]);
            $markup .= $column === $count - 1 ? $rightChar : $midChar;
        }
        if (null !== $title) {
            $titleLength = Helper::width(Helper::removeDecoration($formatter = $this->output->getFormatter(), $formattedTitle = \sprintf($titleFormat, $title)));
            $markupLength = Helper::width($markup);
            if ($titleLength > ($limit = $markupLength - 4)) {
                $titleLength = $limit;
                $formatLength = Helper::width(Helper::removeDecoration($formatter, \sprintf($titleFormat, '')));
                $formattedTitle = \sprintf($titleFormat, Helper::substr($title, 0, $limit - $formatLength - 3) . '...');
            }
            $titleStart = \intdiv($markupLength - $titleLength, 2);
            if (\false === \mb_detect_encoding($markup, null, \true)) {
                $markup = \substr_replace($markup, $formattedTitle, $titleStart, $titleLength);
            } else {
                $markup = \mb_substr($markup, 0, $titleStart) . $formattedTitle . \mb_substr($markup, $titleStart + $titleLength);
            }
        }
        $this->output->writeln(\sprintf($this->style->getBorderFormat(), $markup));
    }
    private function renderColumnSeparator(int $type = self::BORDER_OUTSIDE) : string
    {
        $borders = $this->style->getBorderChars();
        return \sprintf($this->style->getBorderFormat(), self::BORDER_OUTSIDE === $type ? $borders[1] : $borders[3]);
    }
    private function renderRow(array $row, string $cellFormat, string $firstCellFormat = null)
    {
        $rowContent = $this->renderColumnSeparator(self::BORDER_OUTSIDE);
        $columns = $this->getRowColumns($row);
        $last = \count($columns) - 1;
        foreach ($columns as $i => $column) {
            if ($firstCellFormat && 0 === $i) {
                $rowContent .= $this->renderCell($row, $column, $firstCellFormat);
            } else {
                $rowContent .= $this->renderCell($row, $column, $cellFormat);
            }
            $rowContent .= $this->renderColumnSeparator($last === $i ? self::BORDER_OUTSIDE : self::BORDER_INSIDE);
        }
        $this->output->writeln($rowContent);
    }
    private function renderCell(array $row, int $column, string $cellFormat) : string
    {
        $cell = $row[$column] ?? '';
        $width = $this->effectiveColumnWidths[$column];
        if ($cell instanceof TableCell && $cell->getColspan() > 1) {
            foreach (\range($column + 1, $column + $cell->getColspan() - 1) as $nextColumn) {
                $width += $this->getColumnSeparatorWidth() + $this->effectiveColumnWidths[$nextColumn];
            }
        }
        if (\false !== ($encoding = \mb_detect_encoding($cell, null, \true))) {
            $width += \strlen($cell) - \mb_strwidth($cell, $encoding);
        }
        $style = $this->getColumnStyle($column);
        if ($cell instanceof TableSeparator) {
            return \sprintf($style->getBorderFormat(), \str_repeat($style->getBorderChars()[2], $width));
        }
        $width += Helper::length($cell) - Helper::length(Helper::removeDecoration($this->output->getFormatter(), $cell));
        $content = \sprintf($style->getCellRowContentFormat(), $cell);
        $padType = $style->getPadType();
        if ($cell instanceof TableCell && $cell->getStyle() instanceof TableCellStyle) {
            $isNotStyledByTag = !\preg_match('/^<(\\w+|(\\w+=[\\w,]+;?)*)>.+<\\/(\\w+|(\\w+=\\w+;?)*)?>$/', $cell);
            if ($isNotStyledByTag) {
                $cellFormat = $cell->getStyle()->getCellFormat();
                if (!\is_string($cellFormat)) {
                    $tag = \http_build_query($cell->getStyle()->getTagOptions(), '', ';');
                    $cellFormat = '<' . $tag . '>%s</>';
                }
                if (\strstr($content, '</>')) {
                    $content = \str_replace('</>', '', $content);
                    $width -= 3;
                }
                if (\strstr($content, '<fg=default;bg=default>')) {
                    $content = \str_replace('<fg=default;bg=default>', '', $content);
                    $width -= \strlen('<fg=default;bg=default>');
                }
            }
            $padType = $cell->getStyle()->getPadByAlign();
        }
        return \sprintf($cellFormat, \str_pad($content, $width, $style->getPaddingChar(), $padType));
    }
    private function calculateNumberOfColumns(array $rows)
    {
        $columns = [0];
        foreach ($rows as $row) {
            if ($row instanceof TableSeparator) {
                continue;
            }
            $columns[] = $this->getNumberOfColumns($row);
        }
        $this->numberOfColumns = \max($columns);
    }
    private function buildTableRows(array $rows) : TableRows
    {
        $formatter = $this->output->getFormatter();
        $unmergedRows = [];
        for ($rowKey = 0; $rowKey < \count($rows); ++$rowKey) {
            $rows = $this->fillNextRows($rows, $rowKey);
            foreach ($rows[$rowKey] as $column => $cell) {
                $colspan = $cell instanceof TableCell ? $cell->getColspan() : 1;
                if (isset($this->columnMaxWidths[$column]) && Helper::width(Helper::removeDecoration($formatter, $cell)) > $this->columnMaxWidths[$column]) {
                    $cell = $formatter->formatAndWrap($cell, $this->columnMaxWidths[$column] * $colspan);
                }
                if (!\strstr($cell ?? '', "\n")) {
                    continue;
                }
                $escaped = \implode("\n", \array_map([OutputFormatter::class, 'escapeTrailingBackslash'], \explode("\n", $cell)));
                $cell = $cell instanceof TableCell ? new TableCell($escaped, ['colspan' => $cell->getColspan()]) : $escaped;
                $lines = \explode("\n", \str_replace("\n", "<fg=default;bg=default></>\n", $cell));
                foreach ($lines as $lineKey => $line) {
                    if ($colspan > 1) {
                        $line = new TableCell($line, ['colspan' => $colspan]);
                    }
                    if (0 === $lineKey) {
                        $rows[$rowKey][$column] = $line;
                    } else {
                        if (!\array_key_exists($rowKey, $unmergedRows) || !\array_key_exists($lineKey, $unmergedRows[$rowKey])) {
                            $unmergedRows[$rowKey][$lineKey] = $this->copyRow($rows, $rowKey);
                        }
                        $unmergedRows[$rowKey][$lineKey][$column] = $line;
                    }
                }
            }
        }
        return new TableRows(function () use($rows, $unmergedRows) : \Traversable {
            foreach ($rows as $rowKey => $row) {
                $rowGroup = [$row instanceof TableSeparator ? $row : $this->fillCells($row)];
                if (isset($unmergedRows[$rowKey])) {
                    foreach ($unmergedRows[$rowKey] as $row) {
                        $rowGroup[] = $row instanceof TableSeparator ? $row : $this->fillCells($row);
                    }
                }
                (yield $rowGroup);
            }
        });
    }
    private function calculateRowCount() : int
    {
        $numberOfRows = \count(\iterator_to_array($this->buildTableRows(\array_merge($this->headers, [new TableSeparator()], $this->rows))));
        if ($this->headers) {
            ++$numberOfRows;
        }
        if (\count($this->rows) > 0) {
            ++$numberOfRows;
        }
        return $numberOfRows;
    }
    private function fillNextRows(array $rows, int $line) : array
    {
        $unmergedRows = [];
        foreach ($rows[$line] as $column => $cell) {
            if (null !== $cell && !$cell instanceof TableCell && !\is_scalar($cell) && !(\is_object($cell) && \method_exists($cell, '__toString'))) {
                throw new InvalidArgumentException(\sprintf('A cell must be a TableCell, a scalar or an object implementing "__toString()", "%s" given.', \get_debug_type($cell)));
            }
            if ($cell instanceof TableCell && $cell->getRowspan() > 1) {
                $nbLines = $cell->getRowspan() - 1;
                $lines = [$cell];
                if (\strstr($cell, "\n")) {
                    $lines = \explode("\n", \str_replace("\n", "<fg=default;bg=default>\n</>", $cell));
                    $nbLines = \count($lines) > $nbLines ? \substr_count($cell, "\n") : $nbLines;
                    $rows[$line][$column] = new TableCell($lines[0], ['colspan' => $cell->getColspan(), 'style' => $cell->getStyle()]);
                    unset($lines[0]);
                }
                $unmergedRows = \array_replace_recursive(\array_fill($line + 1, $nbLines, []), $unmergedRows);
                foreach ($unmergedRows as $unmergedRowKey => $unmergedRow) {
                    $value = $lines[$unmergedRowKey - $line] ?? '';
                    $unmergedRows[$unmergedRowKey][$column] = new TableCell($value, ['colspan' => $cell->getColspan(), 'style' => $cell->getStyle()]);
                    if ($nbLines === $unmergedRowKey - $line) {
                        break;
                    }
                }
            }
        }
        foreach ($unmergedRows as $unmergedRowKey => $unmergedRow) {
            if (isset($rows[$unmergedRowKey]) && \is_array($rows[$unmergedRowKey]) && $this->getNumberOfColumns($rows[$unmergedRowKey]) + $this->getNumberOfColumns($unmergedRows[$unmergedRowKey]) <= $this->numberOfColumns) {
                foreach ($unmergedRow as $cellKey => $cell) {
                    \array_splice($rows[$unmergedRowKey], $cellKey, 0, [$cell]);
                }
            } else {
                $row = $this->copyRow($rows, $unmergedRowKey - 1);
                foreach ($unmergedRow as $column => $cell) {
                    if (!empty($cell)) {
                        $row[$column] = $unmergedRow[$column];
                    }
                }
                \array_splice($rows, $unmergedRowKey, 0, [$row]);
            }
        }
        return $rows;
    }
    private function fillCells(iterable $row)
    {
        $newRow = [];
        foreach ($row as $column => $cell) {
            $newRow[] = $cell;
            if ($cell instanceof TableCell && $cell->getColspan() > 1) {
                foreach (\range($column + 1, $column + $cell->getColspan() - 1) as $position) {
                    $newRow[] = '';
                }
            }
        }
        return $newRow ?: $row;
    }
    private function copyRow(array $rows, int $line) : array
    {
        $row = $rows[$line];
        foreach ($row as $cellKey => $cellValue) {
            $row[$cellKey] = '';
            if ($cellValue instanceof TableCell) {
                $row[$cellKey] = new TableCell('', ['colspan' => $cellValue->getColspan()]);
            }
        }
        return $row;
    }
    private function getNumberOfColumns(array $row) : int
    {
        $columns = \count($row);
        foreach ($row as $column) {
            $columns += $column instanceof TableCell ? $column->getColspan() - 1 : 0;
        }
        return $columns;
    }
    private function getRowColumns(array $row) : array
    {
        $columns = \range(0, $this->numberOfColumns - 1);
        foreach ($row as $cellKey => $cell) {
            if ($cell instanceof TableCell && $cell->getColspan() > 1) {
                $columns = \array_diff($columns, \range($cellKey + 1, $cellKey + $cell->getColspan() - 1));
            }
        }
        return $columns;
    }
    private function calculateColumnsWidth(iterable $groups)
    {
        for ($column = 0; $column < $this->numberOfColumns; ++$column) {
            $lengths = [];
            foreach ($groups as $group) {
                foreach ($group as $row) {
                    if ($row instanceof TableSeparator) {
                        continue;
                    }
                    foreach ($row as $i => $cell) {
                        if ($cell instanceof TableCell) {
                            $textContent = Helper::removeDecoration($this->output->getFormatter(), $cell);
                            $textLength = Helper::width($textContent);
                            if ($textLength > 0) {
                                $contentColumns = \str_split($textContent, \ceil($textLength / $cell->getColspan()));
                                foreach ($contentColumns as $position => $content) {
                                    $row[$i + $position] = $content;
                                }
                            }
                        }
                    }
                    $lengths[] = $this->getCellWidth($row, $column);
                }
            }
            $this->effectiveColumnWidths[$column] = \max($lengths) + Helper::width($this->style->getCellRowContentFormat()) - 2;
        }
    }
    private function getColumnSeparatorWidth() : int
    {
        return Helper::width(\sprintf($this->style->getBorderFormat(), $this->style->getBorderChars()[3]));
    }
    private function getCellWidth(array $row, int $column) : int
    {
        $cellWidth = 0;
        if (isset($row[$column])) {
            $cell = $row[$column];
            $cellWidth = Helper::width(Helper::removeDecoration($this->output->getFormatter(), $cell));
        }
        $columnWidth = $this->columnWidths[$column] ?? 0;
        $cellWidth = \max($cellWidth, $columnWidth);
        return isset($this->columnMaxWidths[$column]) ? \min($this->columnMaxWidths[$column], $cellWidth) : $cellWidth;
    }
    private function cleanup()
    {
        $this->effectiveColumnWidths = [];
        $this->numberOfColumns = null;
    }
    private static function initStyles() : array
    {
        $borderless = new TableStyle();
        $borderless->setHorizontalBorderChars('=')->setVerticalBorderChars(' ')->setDefaultCrossingChar(' ');
        $compact = new TableStyle();
        $compact->setHorizontalBorderChars('')->setVerticalBorderChars('')->setDefaultCrossingChar('')->setCellRowContentFormat('%s ');
        $styleGuide = new TableStyle();
        $styleGuide->setHorizontalBorderChars('-')->setVerticalBorderChars(' ')->setDefaultCrossingChar(' ')->setCellHeaderFormat('%s');
        $box = (new TableStyle())->setHorizontalBorderChars('─')->setVerticalBorderChars('│')->setCrossingChars('┼', '┌', '┬', '┐', '┤', '┘', '┴', '└', '├');
        $boxDouble = (new TableStyle())->setHorizontalBorderChars('═', '─')->setVerticalBorderChars('║', '│')->setCrossingChars('┼', '╔', '╤', '╗', '╢', '╝', '╧', '╚', '╟', '╠', '╪', '╣');
        return ['default' => new TableStyle(), 'borderless' => $borderless, 'compact' => $compact, 'symfony-style-guide' => $styleGuide, 'box' => $box, 'box-double' => $boxDouble];
    }
    private function resolveStyle($name) : TableStyle
    {
        if ($name instanceof TableStyle) {
            return $name;
        }
        if (isset(self::$styles[$name])) {
            return self::$styles[$name];
        }
        throw new InvalidArgumentException(\sprintf('Style "%s" is not defined.', $name));
    }
}
