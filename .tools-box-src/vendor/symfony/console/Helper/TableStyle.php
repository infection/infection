<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\LogicException;
class TableStyle
{
    private string $paddingChar = ' ';
    private string $horizontalOutsideBorderChar = '-';
    private string $horizontalInsideBorderChar = '-';
    private string $verticalOutsideBorderChar = '|';
    private string $verticalInsideBorderChar = '|';
    private string $crossingChar = '+';
    private string $crossingTopRightChar = '+';
    private string $crossingTopMidChar = '+';
    private string $crossingTopLeftChar = '+';
    private string $crossingMidRightChar = '+';
    private string $crossingBottomRightChar = '+';
    private string $crossingBottomMidChar = '+';
    private string $crossingBottomLeftChar = '+';
    private string $crossingMidLeftChar = '+';
    private string $crossingTopLeftBottomChar = '+';
    private string $crossingTopMidBottomChar = '+';
    private string $crossingTopRightBottomChar = '+';
    private string $headerTitleFormat = '<fg=black;bg=white;options=bold> %s </>';
    private string $footerTitleFormat = '<fg=black;bg=white;options=bold> %s </>';
    private string $cellHeaderFormat = '<info>%s</info>';
    private string $cellRowFormat = '%s';
    private string $cellRowContentFormat = ' %s ';
    private string $borderFormat = '%s';
    private int $padType = \STR_PAD_RIGHT;
    public function setPaddingChar(string $paddingChar) : static
    {
        if (!$paddingChar) {
            throw new LogicException('The padding char must not be empty.');
        }
        $this->paddingChar = $paddingChar;
        return $this;
    }
    public function getPaddingChar() : string
    {
        return $this->paddingChar;
    }
    public function setHorizontalBorderChars(string $outside, string $inside = null) : static
    {
        $this->horizontalOutsideBorderChar = $outside;
        $this->horizontalInsideBorderChar = $inside ?? $outside;
        return $this;
    }
    public function setVerticalBorderChars(string $outside, string $inside = null) : static
    {
        $this->verticalOutsideBorderChar = $outside;
        $this->verticalInsideBorderChar = $inside ?? $outside;
        return $this;
    }
    public function getBorderChars() : array
    {
        return [$this->horizontalOutsideBorderChar, $this->verticalOutsideBorderChar, $this->horizontalInsideBorderChar, $this->verticalInsideBorderChar];
    }
    public function setCrossingChars(string $cross, string $topLeft, string $topMid, string $topRight, string $midRight, string $bottomRight, string $bottomMid, string $bottomLeft, string $midLeft, string $topLeftBottom = null, string $topMidBottom = null, string $topRightBottom = null) : static
    {
        $this->crossingChar = $cross;
        $this->crossingTopLeftChar = $topLeft;
        $this->crossingTopMidChar = $topMid;
        $this->crossingTopRightChar = $topRight;
        $this->crossingMidRightChar = $midRight;
        $this->crossingBottomRightChar = $bottomRight;
        $this->crossingBottomMidChar = $bottomMid;
        $this->crossingBottomLeftChar = $bottomLeft;
        $this->crossingMidLeftChar = $midLeft;
        $this->crossingTopLeftBottomChar = $topLeftBottom ?? $midLeft;
        $this->crossingTopMidBottomChar = $topMidBottom ?? $cross;
        $this->crossingTopRightBottomChar = $topRightBottom ?? $midRight;
        return $this;
    }
    public function setDefaultCrossingChar(string $char) : self
    {
        return $this->setCrossingChars($char, $char, $char, $char, $char, $char, $char, $char, $char);
    }
    public function getCrossingChar() : string
    {
        return $this->crossingChar;
    }
    public function getCrossingChars() : array
    {
        return [$this->crossingChar, $this->crossingTopLeftChar, $this->crossingTopMidChar, $this->crossingTopRightChar, $this->crossingMidRightChar, $this->crossingBottomRightChar, $this->crossingBottomMidChar, $this->crossingBottomLeftChar, $this->crossingMidLeftChar, $this->crossingTopLeftBottomChar, $this->crossingTopMidBottomChar, $this->crossingTopRightBottomChar];
    }
    public function setCellHeaderFormat(string $cellHeaderFormat) : static
    {
        $this->cellHeaderFormat = $cellHeaderFormat;
        return $this;
    }
    public function getCellHeaderFormat() : string
    {
        return $this->cellHeaderFormat;
    }
    public function setCellRowFormat(string $cellRowFormat) : static
    {
        $this->cellRowFormat = $cellRowFormat;
        return $this;
    }
    public function getCellRowFormat() : string
    {
        return $this->cellRowFormat;
    }
    public function setCellRowContentFormat(string $cellRowContentFormat) : static
    {
        $this->cellRowContentFormat = $cellRowContentFormat;
        return $this;
    }
    public function getCellRowContentFormat() : string
    {
        return $this->cellRowContentFormat;
    }
    public function setBorderFormat(string $borderFormat) : static
    {
        $this->borderFormat = $borderFormat;
        return $this;
    }
    public function getBorderFormat() : string
    {
        return $this->borderFormat;
    }
    public function setPadType(int $padType) : static
    {
        if (!\in_array($padType, [\STR_PAD_LEFT, \STR_PAD_RIGHT, \STR_PAD_BOTH], \true)) {
            throw new InvalidArgumentException('Invalid padding type. Expected one of (STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH).');
        }
        $this->padType = $padType;
        return $this;
    }
    public function getPadType() : int
    {
        return $this->padType;
    }
    public function getHeaderTitleFormat() : string
    {
        return $this->headerTitleFormat;
    }
    public function setHeaderTitleFormat(string $format) : static
    {
        $this->headerTitleFormat = $format;
        return $this;
    }
    public function getFooterTitleFormat() : string
    {
        return $this->footerTitleFormat;
    }
    public function setFooterTitleFormat(string $format) : static
    {
        $this->footerTitleFormat = $format;
        return $this;
    }
}
