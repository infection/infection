<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Description;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen as FqsenRef;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Reference\Reference;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Fqsen;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\FqsenResolver;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Context as TypeContext;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Utils;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use function array_key_exists;
use function explode;
use function preg_match;
final class See extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'see';
    protected $refers;
    public function __construct(Reference $refers, ?Description $description = null)
    {
        $this->refers = $refers;
        $this->description = $description;
    }
    public static function create(string $body, ?FqsenResolver $typeResolver = null, ?DescriptionFactory $descriptionFactory = null, ?TypeContext $context = null) : self
    {
        Assert::notNull($descriptionFactory);
        $parts = Utils::pregSplit('/\\s+/Su', $body, 2);
        $description = isset($parts[1]) ? $descriptionFactory->create($parts[1], $context) : null;
        if (preg_match('#\\w://\\w#', $parts[0])) {
            return new static(new Url($parts[0]), $description);
        }
        return new static(new FqsenRef(self::resolveFqsen($parts[0], $typeResolver, $context)), $description);
    }
    private static function resolveFqsen(string $parts, ?FqsenResolver $fqsenResolver, ?TypeContext $context) : Fqsen
    {
        Assert::notNull($fqsenResolver);
        $fqsenParts = explode('::', $parts);
        $resolved = $fqsenResolver->resolve($fqsenParts[0], $context);
        if (!array_key_exists(1, $fqsenParts)) {
            return $resolved;
        }
        return new Fqsen($resolved . '::' . $fqsenParts[1]);
    }
    public function getReference() : Reference
    {
        return $this->refers;
    }
    public function __toString() : string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        $refers = (string) $this->refers;
        return $refers . ($description !== '' ? ($refers !== '' ? ' ' : '') . $description : '');
    }
}
