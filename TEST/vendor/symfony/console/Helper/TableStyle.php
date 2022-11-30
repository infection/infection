<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\LogicException;
class TableStyle
{
    private $paddingChar = ' ';
    private $horizontalOutsideBorderChar = '-';
    private $horizontalInsideBorderChar = '-';
    private $verticalOutsideBorderChar = '|';
    private $verticalInsideBorderChar = '|';
    private $crossingChar = '+';
    private $crossingTopRightChar = '+';
    private $crossingTopMidChar = '+';
    private $crossingTopLeftChar = '+';
    private $crossingMidRightChar = '+';
    private $crossingBottomRightChar = '+';
    private $crossingBottomMidChar = '+';
    private $crossingBottomLeftChar = '+';
    private $crossingMidLeftChar = '+';
    private $crossingTopLeftBottomChar = '+';
    private $crossingTopMidBottomChar = '+';
    private $crossingTopRightBottomChar = '+';
    private $headerTitleFormat = '<fg=black;bg=white;options=bold> %s </>';
    private $footerTitleFormat = '<fg=black;bg=white;options=bold> %s </>';
    private $cellHeaderFormat = '<info>%s</info>';
    private $cellRowFormat = '%s';
    private $cellRowContentFormat = ' %s ';
    private $borderFormat = '%s';
    private $padType = \STR_PAD_RIGHT;
    public function setPaddingChar(string $paddingChar)
    {
        if (!$paddingChar) {
            throw new LogicException('The padding char must not be empty.');
        }
        $this->paddingChar = $paddingChar;
        return $this;
    }
    public function getPaddingChar()
    {
        return $this->paddingChar;
    }
    public function setHorizontalBorderChars(string $outside, string $inside = null) : self
    {
        $this->horizontalOutsideBorderChar = $outside;
        $this->horizontalInsideBorderChar = $inside ?? $outside;
        return $this;
    }
    public function setVerticalBorderChars(string $outside, string $inside = null) : self
    {
        $this->verticalOutsideBorderChar = $outside;
        $this->verticalInsideBorderChar = $inside ?? $outside;
        return $this;
    }
    public function getBorderChars() : array
    {
        return [$this->horizontalOutsideBorderChar, $this->verticalOutsideBorderChar, $this->horizontalInsideBorderChar, $this->verticalInsideBorderChar];
    }
    public function setCrossingChars(string $cross, string $topLeft, string $topMid, string $topRight, string $midRight, string $bottomRight, string $bottomMid, string $bottomLeft, string $midLeft, string $topLeftBottom = null, string $topMidBottom = null, string $topRightBottom = null) : self
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
    public function getCrossingChar()
    {
        return $this->crossingChar;
    }
    public function getCrossingChars() : array
    {
        return [$this->crossingChar, $this->crossingTopLeftChar, $this->crossingTopMidChar, $this->crossingTopRightChar, $this->crossingMidRightChar, $this->crossingBottomRightChar, $this->crossingBottomMidChar, $this->crossingBottomLeftChar, $this->crossingMidLeftChar, $this->crossingTopLeftBottomChar, $this->crossingTopMidBottomChar, $this->crossingTopRightBottomChar];
    }
    public function setCellHeaderFormat(string $cellHeaderFormat)
    {
        $this->cellHeaderFormat = $cellHeaderFormat;
        return $this;
    }
    public function getCellHeaderFormat()
    {
        return $this->cellHeaderFormat;
    }
    public function setCellRowFormat(string $cellRowFormat)
    {
        $this->cellRowFormat = $cellRowFormat;
        return $this;
    }
    public function getCellRowFormat()
    {
        return $this->cellRowFormat;
    }
    public function setCellRowContentFormat(string $cellRowContentFormat)
    {
        $this->cellRowContentFormat = $cellRowContentFormat;
        return $this;
    }
    public function getCellRowContentFormat()
    {
        return $this->cellRowContentFormat;
    }
    public function setBorderFormat(string $borderFormat)
    {
        $this->borderFormat = $borderFormat;
        return $this;
    }
    public function getBorderFormat()
    {
        return $this->borderFormat;
    }
    public function setPadType(int $padType)
    {
        if (!\in_array($padType, [\STR_PAD_LEFT, \STR_PAD_RIGHT, \STR_PAD_BOTH], \true)) {
            throw new InvalidArgumentException('Invalid padding type. Expected one of (STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH).');
        }
        $this->padType = $padType;
        return $this;
    }
    public function getPadType()
    {
        return $this->padType;
    }
    public function getHeaderTitleFormat() : string
    {
        return $this->headerTitleFormat;
    }
    public function setHeaderTitleFormat(string $format) : self
    {
        $this->headerTitleFormat = $format;
        return $this;
    }
    public function getFooterTitleFormat() : string
    {
        return $this->footerTitleFormat;
    }
    public function setFooterTitleFormat(string $format) : self
    {
        $this->footerTitleFormat = $format;
        return $this;
    }
}
