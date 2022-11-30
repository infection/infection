<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Annotation;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_values;
use InvalidArgumentException;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tag;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlockFactoryInterface;
use function _HumbugBoxb47773b41c19\Safe\array_flip;
use function strtolower;
final class DocblockAnnotationParser
{
    private array $ignoredAnnotationsAsKeys;
    public function __construct(private DocBlockFactoryInterface $factory, private Formatter $tagsFormatter, array $ignoredAnnotations)
    {
        $this->ignoredAnnotationsAsKeys = array_flip($ignoredAnnotations);
    }
    public function parse(string $docblock) : array
    {
        $doc = $this->createDocBlock($docblock);
        $tags = self::extractTags($doc, $this->ignoredAnnotationsAsKeys);
        return array_map(fn(Tag $tag) => $tag->render($this->tagsFormatter), $tags);
    }
    private function createDocBlock(string $docblock) : DocBlock
    {
        try {
            return $this->factory->create($docblock);
        } catch (InvalidArgumentException $invalidDocBlock) {
            throw new MalformedTagException('The annotations could not be parsed.', 0, $invalidDocBlock);
        }
    }
    private static function extractTags(DocBlock $docBlock, array $ignoredAnnotations) : array
    {
        return array_values(array_filter($docBlock->getTags(), static fn(Tag $tag) => !array_key_exists(strtolower($tag->getName()), $ignoredAnnotations)));
    }
}
