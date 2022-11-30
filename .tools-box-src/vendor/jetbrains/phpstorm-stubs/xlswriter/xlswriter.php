<?php







namespace Vtiful\Kernel;






class Excel
{
public const TYPE_STRING = 0x01;
public const TYPE_INT = 0x02;
public const TYPE_DOUBLE = 0x04;
public const TYPE_TIMESTAMP = 0x08;
public const SKIP_NONE = 0x00;
public const SKIP_EMPTY_ROW = 0x01;
public const SKIP_EMPTY_CELLS = 0x02;
public const GRIDLINES_HIDE_ALL = 0;
public const GRIDLINES_SHOW_SCREEN = 1;
public const GRIDLINES_SHOW_PRINT = 2;
public const GRIDLINES_SHOW_ALL = 3;






public function __construct(array $config) {}









public function fileName(string $fileName, string $sheetName = 'Sheet1'): self
{
return $this;
}









public function constMemory(string $fileName, string $sheetName = 'Sheet1'): self
{
return $this;
}
















public function addSheet(?string $sheetName): self
{
return $this;
}








public function checkoutSheet(string $sheetName): self
{
return $this;
}








public function header(array $header): self
{
return $this;
}








public function data(array $data): self
{
return $this;
}






public function output(): string
{
return 'FilePath';
}






public function getHandle() {}








public function autoFilter(string $range): self
{
return $this;
}












public function insertText(int $row, int $column, $data, string $format = null, $formatHandle = null): self
{
return $this;
}












public function insertDate(int $row, int $column, int $timestamp, string $format = null, $formatHandle = null): self
{
return $this;
}










public function insertChart(int $row, int $column, $chartResource): self
{
return $this;
}











public function insertUrl(int $row, int $column, string $url, $formatHandle = null): self
{
return $this;
}












public function insertImage(int $row, int $column, string $imagePath, float $width = 1, float $height = 1): self
{
return $this;
}










public function insertFormula(int $row, int $column, string $formula): self
{
return $this;
}









public function MergeCells(string $range, string $data): self
{
return $this;
}










public function setColumn(string $range, float $cellWidth, $formatHandle = null): self
{
return $this;
}










public function setRow(string $range, float $cellHeight, $formatHandle = null): self
{
return $this;
}








public function openFile(string $fileName): self
{
return $this;
}











public function openSheet(string $sheetName = null, int $skipFlag = 0x00): self
{
return $this;
}








public function setType(array $types): self
{
return $this;
}






public function getSheetData(): array
{
return [];
}






public function nextRow(): array
{
return [];
}







public function nextCellCallback(callable $callback, string $sheetName = null): void {}













public function freezePanes(int $row, int $column): self
{
return $this;
}

















public function gridline(int $option): self
{
return $this;
}










public function zoom(int $scale): self
{
return $this;
}
}







class Format
{
public const UNDERLINE_SINGLE = 0x00;
public const UNDERLINE_DOUBLE = 0x00;
public const UNDERLINE_SINGLE_ACCOUNTING = 0x00;
public const UNDERLINE_DOUBLE_ACCOUNTING = 0x00;
public const FORMAT_ALIGN_LEFT = 0x00;
public const FORMAT_ALIGN_CENTER = 0x00;
public const FORMAT_ALIGN_RIGHT = 0x00;
public const FORMAT_ALIGN_FILL = 0x00;
public const FORMAT_ALIGN_JUSTIFY = 0x00;
public const FORMAT_ALIGN_CENTER_ACROSS = 0x00;
public const FORMAT_ALIGN_DISTRIBUTED = 0x00;
public const FORMAT_ALIGN_VERTICAL_TOP = 0x00;
public const FORMAT_ALIGN_VERTICAL_BOTTOM = 0x00;
public const FORMAT_ALIGN_VERTICAL_CENTER = 0x00;
public const FORMAT_ALIGN_VERTICAL_JUSTIFY = 0x00;
public const FORMAT_ALIGN_VERTICAL_DISTRIBUTED = 0x00;
public const COLOR_BLACK = 0x00;
public const COLOR_BLUE = 0x00;
public const COLOR_BROWN = 0x00;
public const COLOR_CYAN = 0x00;
public const COLOR_GRAY = 0x00;
public const COLOR_GREEN = 0x00;
public const COLOR_LIME = 0x00;
public const COLOR_MAGENTA = 0x00;
public const COLOR_NAVY = 0x00;
public const COLOR_ORANGE = 0x00;
public const COLOR_PINK = 0x00;
public const COLOR_PURPLE = 0x00;
public const COLOR_RED = 0x00;
public const COLOR_SILVER = 0x00;
public const COLOR_WHITE = 0x00;
public const COLOR_YELLOW = 0x00;
public const PATTERN_NONE = 0x00;
public const PATTERN_SOLID = 0x00;
public const PATTERN_MEDIUM_GRAY = 0x00;
public const PATTERN_DARK_GRAY = 0x00;
public const PATTERN_LIGHT_GRAY = 0x00;
public const PATTERN_DARK_HORIZONTAL = 0x00;
public const PATTERN_DARK_VERTICAL = 0x00;
public const PATTERN_DARK_DOWN = 0x00;
public const PATTERN_DARK_UP = 0x00;
public const PATTERN_DARK_GRID = 0x00;
public const PATTERN_DARK_TRELLIS = 0x00;
public const PATTERN_LIGHT_HORIZONTAL = 0x00;
public const PATTERN_LIGHT_VERTICAL = 0x00;
public const PATTERN_LIGHT_DOWN = 0x00;
public const PATTERN_LIGHT_UP = 0x00;
public const PATTERN_LIGHT_GRID = 0x00;
public const PATTERN_LIGHT_TRELLIS = 0x00;
public const PATTERN_GRAY_125 = 0x00;
public const PATTERN_GRAY_0625 = 0x00;
public const BORDER_THIN = 0x00;
public const BORDER_MEDIUM = 0x00;
public const BORDER_DASHED = 0x00;
public const BORDER_DOTTED = 0x00;
public const BORDER_THICK = 0x00;
public const BORDER_DOUBLE = 0x00;
public const BORDER_HAIR = 0x00;
public const BORDER_MEDIUM_DASHED = 0x00;
public const BORDER_DASH_DOT = 0x00;
public const BORDER_MEDIUM_DASH_DOT = 0x00;
public const BORDER_DASH_DOT_DOT = 0x00;
public const BORDER_MEDIUM_DASH_DOT_DOT = 0x00;
public const BORDER_SLANT_DASH_DOT = 0x00;






public function __construct($fileHandle) {}






public function wrap(): self
{
return $this;
}






public function bold(): self
{
return $this;
}






public function italic(): self
{
return $this;
}








public function border(int $style): self
{
return $this;
}








public function align(...$style): self
{
return $this;
}










public function number(string $format): self
{
return $this;
}








public function fontColor(int $color): self
{
return $this;
}








public function font(string $fontName): self
{
return $this;
}








public function fontSize(float $size): self
{
return $this;
}






public function strikeout(): self
{
return $this;
}








public function underline(int $style): self
{
return $this;
}









public function background(int $color, int $pattern = self::PATTERN_SOLID): self
{
return $this;
}






public function toResource() {}
}
