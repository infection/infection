<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

use _HumbugBoxb47773b41c19\Fidry\Console\InputAssert;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use function sprintf;
/**
@template
@implements
*/
final class NonEmptyListType implements InputType
{
    private InputType $innerType;
    public function __construct(InputType $innerType)
    {
        $this->innerType = $innerType;
    }
    public function coerceValue($value, string $label) : array
    {
        $list = (new ListType($this->innerType))->coerceValue($value, $label);
        /**
        @psalm-suppress */
        InputAssert::castThrowException(static fn() => Assert::minCount($list, 1), $label);
        return $list;
    }
    public function getTypeClassNames() : array
    {
        return [self::class, ...$this->innerType->getTypeClassNames()];
    }
    /**
    @psalm-suppress */
    public function getPsalmTypeDeclaration() : string
    {
        /**
        @psalm-suppress */
        return sprintf('non-empty-list<%s>', $this->innerType->getPsalmTypeDeclaration());
    }
    public function getPhpTypeDeclaration() : ?string
    {
        return 'array';
    }
}
