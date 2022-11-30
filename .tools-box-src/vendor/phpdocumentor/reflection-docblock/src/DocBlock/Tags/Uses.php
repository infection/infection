<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Description;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Fqsen;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\FqsenResolver;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Context as TypeContext;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Utils;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use function array_key_exists;
use function explode;
final class Uses extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'uses';
    protected $refers;
    public function __construct(Fqsen $refers, ?Description $description = null)
    {
        $this->refers = $refers;
        $this->description = $description;
    }
    public static function create(string $body, ?FqsenResolver $resolver = null, ?DescriptionFactory $descriptionFactory = null, ?TypeContext $context = null) : self
    {
        Assert::notNull($resolver);
        Assert::notNull($descriptionFactory);
        $parts = Utils::pregSplit('/\\s+/Su', $body, 2);
        return new static(self::resolveFqsen($parts[0], $resolver, $context), $descriptionFactory->create($parts[1] ?? '', $context));
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
    public function getReference() : Fqsen
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
