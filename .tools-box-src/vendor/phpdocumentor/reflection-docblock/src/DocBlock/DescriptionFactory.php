<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Context as TypeContext;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Utils;
use function count;
use function implode;
use function ltrim;
use function min;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function trim;
use const PREG_SPLIT_DELIM_CAPTURE;
class DescriptionFactory
{
    private $tagFactory;
    public function __construct(TagFactory $tagFactory)
    {
        $this->tagFactory = $tagFactory;
    }
    public function create(string $contents, ?TypeContext $context = null) : Description
    {
        $tokens = $this->lex($contents);
        $count = count($tokens);
        $tagCount = 0;
        $tags = [];
        for ($i = 1; $i < $count; $i += 2) {
            $tags[] = $this->tagFactory->create($tokens[$i], $context);
            $tokens[$i] = '%' . ++$tagCount . '$s';
        }
        for ($i = 0; $i < $count; $i += 2) {
            $tokens[$i] = str_replace(['{@}', '{}', '%'], ['@', '}', '%%'], $tokens[$i]);
        }
        return new Description(implode('', $tokens), $tags);
    }
    private function lex(string $contents) : array
    {
        $contents = $this->removeSuperfluousStartingWhitespace($contents);
        if (strpos($contents, '{@') === \false) {
            return [$contents];
        }
        return Utils::pregSplit('/\\{
                # "{@}" is not a valid inline tag. This ensures that we do not treat it as one, but treat it literally.
                (?!@\\})
                # We want to capture the whole tag line, but without the inline tag delimiters.
                (\\@
                    # Match everything up to the next delimiter.
                    [^{}]*
                    # Nested inline tag content should not be captured, or it will appear in the result separately.
                    (?:
                        # Match nested inline tags.
                        (?:
                            # Because we did not catch the tag delimiters earlier, we must be explicit with them here.
                            # Notice that this also matches "{}", as a way to later introduce it as an escape sequence.
                            \\{(?1)?\\}
                            |
                            # Make sure we match hanging "{".
                            \\{
                        )
                        # Match content after the nested inline tag.
                        [^{}]*
                    )* # If there are more inline tags, match them as well. We use "*" since there may not be any
                       # nested inline tags.
                )
            \\}/Sux', $contents, 0, PREG_SPLIT_DELIM_CAPTURE);
    }
    private function removeSuperfluousStartingWhitespace(string $contents) : string
    {
        $lines = Utils::pregSplit("/\r\n?|\n/", $contents);
        if (count($lines) <= 1) {
            return $contents;
        }
        $startingSpaceCount = 9999999;
        for ($i = 1, $iMax = count($lines); $i < $iMax; ++$i) {
            if (trim($lines[$i]) === '') {
                continue;
            }
            $startingSpaceCount = min($startingSpaceCount, strlen($lines[$i]) - strlen(ltrim($lines[$i])));
        }
        if ($startingSpaceCount > 0) {
            for ($i = 1, $iMax = count($lines); $i < $iMax; ++$i) {
                $lines[$i] = substr($lines[$i], $startingSpaceCount);
            }
        }
        return implode("\n", $lines);
    }
}
