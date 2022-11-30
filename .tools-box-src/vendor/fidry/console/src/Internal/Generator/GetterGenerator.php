<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Generator;

use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\InputType;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use function array_diff;
use function array_map;
use function array_shift;
use function array_unshift;
use function count;
use function explode;
use function implode;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_repeat;
use function str_replace;
final class GetterGenerator
{
    private const TEMPLATE = <<<'PHP'
/**
 * @return __PSALM_RETURN_TYPE_PLACEHOLDER__
 */
public function __METHOD_NAME_PLACEHOLDER__(?string $errorMessage = null)__PHP_RETURN_TYPE_PLACEHOLDER__
{
    $type = TypeFactory::createTypeFromClassNames([
    __TYPE_CLASS_NAMES_PLACEHOLDER__
    ]);

    if (null === $errorMessage) {
        return $type->coerceValue($this->value, $this->label);
    }

    try {
        return $type->coerceValue($this->value, $this->label);
    } catch (InvalidInputValueType $coercingFailed) {
        throw InvalidInputValueType::withErrorMessage(
            $coercingFailed,
            $errorMessage,
        );
    }
}
PHP;
    private const INDENT_SIZE = 4;
    private function __construct()
    {
    }
    public static function generate(InputType $type) : string
    {
        $typeClassNames = $type->getTypeClassNames();
        $psalmTypeDeclaration = $type->getPsalmTypeDeclaration();
        $phpReturnType = $type->getPhpTypeDeclaration();
        if (self::isPsalmTypeRedundant($psalmTypeDeclaration, $phpReturnType)) {
            $psalmTypeDeclaration = '';
        }
        if (null !== $phpReturnType) {
            $phpReturnType = ': ' . $phpReturnType;
        } else {
            $phpReturnType = '';
        }
        $content = str_replace(['__METHOD_NAME_PLACEHOLDER__', '__PSALM_RETURN_TYPE_PLACEHOLDER__', '__PHP_RETURN_TYPE_PLACEHOLDER__', '__TYPE_CLASS_NAMES_PLACEHOLDER__'], [GetterNameGenerator::generateMethodName($typeClassNames), $psalmTypeDeclaration, $phpReturnType, self::serializeTypeNames($typeClassNames)], self::TEMPLATE);
        return self::removeEmptyReturn($content);
    }
    private static function serializeTypeNames(array $typeClassNames) : string
    {
        $firstTypeClassName = array_shift($typeClassNames);
        $formattedTypeClassNames = array_map(static fn(string $typeClassName) => self::formatTypeClassName($typeClassName, 2), $typeClassNames);
        array_unshift($formattedTypeClassNames, self::formatTypeClassName($firstTypeClassName, 1));
        return implode("\n", $formattedTypeClassNames);
    }
    private static function formatTypeClassName(string $typeClassName, int $indentSize) : string
    {
        return sprintf('%s\\%s::class,', str_repeat(' ', self::INDENT_SIZE * $indentSize), $typeClassName);
    }
    private static function isPsalmTypeRedundant(string $psalmTypeDeclaration, ?string $phpReturnType) : bool
    {
        if (null === $phpReturnType || preg_match('/.+<.+>/', $psalmTypeDeclaration)) {
            return \false;
        }
        $psalmTypes = explode('|', $psalmTypeDeclaration);
        $phpTypes = explode('|', str_replace('?', 'null|', $phpReturnType));
        $extraPsalmTypes = array_diff($psalmTypes, $phpTypes);
        $extraPhpTypes = array_diff($phpTypes, $psalmTypes);
        return 0 === count($extraPsalmTypes) && 0 === count($extraPhpTypes);
    }
    private static function removeEmptyReturn(string $value) : string
    {
        $value = preg_replace('#\\/\\*\\*[\\s\\n]+\\* @return\\s?[\\s\\n]+\\*\\/#', '', $value);
        Assert::string($value);
        return $value;
    }
}
