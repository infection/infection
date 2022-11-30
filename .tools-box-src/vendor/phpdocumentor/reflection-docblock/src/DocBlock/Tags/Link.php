<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Description;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Context as TypeContext;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Utils;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class Link extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'link';
    private $link;
    public function __construct(string $link, ?Description $description = null)
    {
        $this->link = $link;
        $this->description = $description;
    }
    public static function create(string $body, ?DescriptionFactory $descriptionFactory = null, ?TypeContext $context = null) : self
    {
        Assert::notNull($descriptionFactory);
        $parts = Utils::pregSplit('/\\s+/Su', $body, 2);
        $description = isset($parts[1]) ? $descriptionFactory->create($parts[1], $context) : null;
        return new static($parts[0], $description);
    }
    public function getLink() : string
    {
        return $this->link;
    }
    public function __toString() : string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        $link = $this->link;
        return $link . ($description !== '' ? ($link !== '' ? ' ' : '') . $description : '');
    }
}
