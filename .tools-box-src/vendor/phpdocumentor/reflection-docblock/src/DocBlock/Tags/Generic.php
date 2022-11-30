<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags;

use InvalidArgumentException;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Description;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Context as TypeContext;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use function preg_match;
final class Generic extends BaseTag implements Factory\StaticMethod
{
    public function __construct(string $name, ?Description $description = null)
    {
        $this->validateTagName($name);
        $this->name = $name;
        $this->description = $description;
    }
    public static function create(string $body, string $name = '', ?DescriptionFactory $descriptionFactory = null, ?TypeContext $context = null) : self
    {
        Assert::stringNotEmpty($name);
        Assert::notNull($descriptionFactory);
        $description = $body !== '' ? $descriptionFactory->create($body, $context) : null;
        return new static($name, $description);
    }
    public function __toString() : string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        return $description;
    }
    private function validateTagName(string $name) : void
    {
        if (!preg_match('/^' . StandardTagFactory::REGEX_TAGNAME . '$/u', $name)) {
            throw new InvalidArgumentException('The tag name "' . $name . '" is not wellformed. Tags may only consist of letters, underscores, ' . 'hyphens and backslashes.');
        }
    }
}
