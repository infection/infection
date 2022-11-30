<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter;
use function sprintf;
use function str_repeat;
use function str_replace;
use function strlen;
use function wordwrap;
class Serializer
{
    protected $indentString = ' ';
    protected $indent = 0;
    protected $isFirstLineIndented = \true;
    protected $lineLength;
    protected $tagFormatter;
    private $lineEnding;
    public function __construct(int $indent = 0, string $indentString = ' ', bool $indentFirstLine = \true, ?int $lineLength = null, ?Formatter $tagFormatter = null, string $lineEnding = "\n")
    {
        $this->indent = $indent;
        $this->indentString = $indentString;
        $this->isFirstLineIndented = $indentFirstLine;
        $this->lineLength = $lineLength;
        $this->tagFormatter = $tagFormatter ?: new PassthroughFormatter();
        $this->lineEnding = $lineEnding;
    }
    public function getDocComment(DocBlock $docblock) : string
    {
        $indent = str_repeat($this->indentString, $this->indent);
        $firstIndent = $this->isFirstLineIndented ? $indent : '';
        $wrapLength = $this->lineLength ? $this->lineLength - strlen($indent) - 3 : null;
        $text = $this->removeTrailingSpaces($indent, $this->addAsterisksForEachLine($indent, $this->getSummaryAndDescriptionTextBlock($docblock, $wrapLength)));
        $comment = $firstIndent . "/**\n";
        if ($text) {
            $comment .= $indent . ' * ' . $text . "\n";
            $comment .= $indent . " *\n";
        }
        $comment = $this->addTagBlock($docblock, $wrapLength, $indent, $comment);
        return str_replace("\n", $this->lineEnding, $comment . $indent . ' */');
    }
    private function removeTrailingSpaces(string $indent, string $text) : string
    {
        return str_replace(sprintf("\n%s * \n", $indent), sprintf("\n%s *\n", $indent), $text);
    }
    private function addAsterisksForEachLine(string $indent, string $text) : string
    {
        return str_replace("\n", sprintf("\n%s * ", $indent), $text);
    }
    private function getSummaryAndDescriptionTextBlock(DocBlock $docblock, ?int $wrapLength) : string
    {
        $text = $docblock->getSummary() . ((string) $docblock->getDescription() ? "\n\n" . $docblock->getDescription() : '');
        if ($wrapLength !== null) {
            $text = wordwrap($text, $wrapLength);
            return $text;
        }
        return $text;
    }
    private function addTagBlock(DocBlock $docblock, ?int $wrapLength, string $indent, string $comment) : string
    {
        foreach ($docblock->getTags() as $tag) {
            $tagText = $this->tagFormatter->format($tag);
            if ($wrapLength !== null) {
                $tagText = wordwrap($tagText, $wrapLength);
            }
            $tagText = str_replace("\n", sprintf("\n%s * ", $indent), $tagText);
            $comment .= sprintf("%s * %s\n", $indent, $tagText);
        }
        return $comment;
    }
}
