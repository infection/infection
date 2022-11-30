<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

use ArrayIterator;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use RuntimeException;
use UnexpectedValueException;
use function define;
use function defined;
use function file_exists;
use function file_get_contents;
use function get_class;
use function in_array;
use function is_string;
use function strrpos;
use function substr;
use function token_get_all;
use function trim;
use const T_AS;
use const T_CLASS;
use const T_CURLY_OPEN;
use const T_DOLLAR_OPEN_CURLY_BRACES;
use const T_NAME_FULLY_QUALIFIED;
use const T_NAME_QUALIFIED;
use const T_NAMESPACE;
use const T_NS_SEPARATOR;
use const T_STRING;
use const T_USE;
if (!defined('T_NAME_QUALIFIED')) {
    define('T_NAME_QUALIFIED', 'T_NAME_QUALIFIED');
}
if (!defined('T_NAME_FULLY_QUALIFIED')) {
    define('T_NAME_FULLY_QUALIFIED', 'T_NAME_FULLY_QUALIFIED');
}
final class ContextFactory
{
    private const T_LITERAL_END_OF_USE = ';';
    private const T_LITERAL_USE_SEPARATOR = ',';
    public function createFromReflector(Reflector $reflector) : Context
    {
        if ($reflector instanceof ReflectionClass) {
            return $this->createFromReflectionClass($reflector);
        }
        if ($reflector instanceof ReflectionParameter) {
            return $this->createFromReflectionParameter($reflector);
        }
        if ($reflector instanceof ReflectionMethod) {
            return $this->createFromReflectionMethod($reflector);
        }
        if ($reflector instanceof ReflectionProperty) {
            return $this->createFromReflectionProperty($reflector);
        }
        if ($reflector instanceof ReflectionClassConstant) {
            return $this->createFromReflectionClassConstant($reflector);
        }
        throw new UnexpectedValueException('Unhandled \\Reflector instance given:  ' . get_class($reflector));
    }
    private function createFromReflectionParameter(ReflectionParameter $parameter) : Context
    {
        $class = $parameter->getDeclaringClass();
        if (!$class) {
            throw new InvalidArgumentException('Unable to get class of ' . $parameter->getName());
        }
        return $this->createFromReflectionClass($class);
    }
    private function createFromReflectionMethod(ReflectionMethod $method) : Context
    {
        $class = $method->getDeclaringClass();
        return $this->createFromReflectionClass($class);
    }
    private function createFromReflectionProperty(ReflectionProperty $property) : Context
    {
        $class = $property->getDeclaringClass();
        return $this->createFromReflectionClass($class);
    }
    private function createFromReflectionClassConstant(ReflectionClassConstant $constant) : Context
    {
        /**
        @phpstan-var */
        $class = $constant->getDeclaringClass();
        return $this->createFromReflectionClass($class);
    }
    /**
    @phpstan-param
    */
    private function createFromReflectionClass(ReflectionClass $class) : Context
    {
        $fileName = $class->getFileName();
        $namespace = $class->getNamespaceName();
        if (is_string($fileName) && file_exists($fileName)) {
            $contents = file_get_contents($fileName);
            if ($contents === \false) {
                throw new RuntimeException('Unable to read file "' . $fileName . '"');
            }
            return $this->createForNamespace($namespace, $contents);
        }
        return new Context($namespace, []);
    }
    public function createForNamespace(string $namespace, string $fileContents) : Context
    {
        $namespace = trim($namespace, '\\');
        $useStatements = [];
        $currentNamespace = '';
        $tokens = new ArrayIterator(token_get_all($fileContents));
        while ($tokens->valid()) {
            $currentToken = $tokens->current();
            switch ($currentToken[0]) {
                case T_NAMESPACE:
                    $currentNamespace = $this->parseNamespace($tokens);
                    break;
                case T_CLASS:
                    $braceLevel = 0;
                    $firstBraceFound = \false;
                    while ($tokens->valid() && ($braceLevel > 0 || !$firstBraceFound)) {
                        $currentToken = $tokens->current();
                        if ($currentToken === '{' || in_array($currentToken[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], \true)) {
                            if (!$firstBraceFound) {
                                $firstBraceFound = \true;
                            }
                            ++$braceLevel;
                        }
                        if ($currentToken === '}') {
                            --$braceLevel;
                        }
                        $tokens->next();
                    }
                    break;
                case T_USE:
                    if ($currentNamespace === $namespace) {
                        $useStatements += $this->parseUseStatement($tokens);
                    }
                    break;
            }
            $tokens->next();
        }
        return new Context($namespace, $useStatements);
    }
    private function parseNamespace(ArrayIterator $tokens) : string
    {
        $this->skipToNextStringOrNamespaceSeparator($tokens);
        $name = '';
        $acceptedTokens = [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED];
        while ($tokens->valid() && in_array($tokens->current()[0], $acceptedTokens, \true)) {
            $name .= $tokens->current()[1];
            $tokens->next();
        }
        return $name;
    }
    /**
    @psalm-return
    */
    private function parseUseStatement(ArrayIterator $tokens) : array
    {
        $uses = [];
        while ($tokens->valid()) {
            $this->skipToNextStringOrNamespaceSeparator($tokens);
            $uses += $this->extractUseStatements($tokens);
            $currentToken = $tokens->current();
            if ($currentToken[0] === self::T_LITERAL_END_OF_USE) {
                return $uses;
            }
        }
        return $uses;
    }
    private function skipToNextStringOrNamespaceSeparator(ArrayIterator $tokens) : void
    {
        while ($tokens->valid()) {
            $currentToken = $tokens->current();
            if (in_array($currentToken[0], [T_STRING, T_NS_SEPARATOR], \true)) {
                break;
            }
            if ($currentToken[0] === T_NAME_QUALIFIED) {
                break;
            }
            if (defined('T_NAME_FULLY_QUALIFIED') && $currentToken[0] === T_NAME_FULLY_QUALIFIED) {
                break;
            }
            $tokens->next();
        }
    }
    /**
    @psalm-return
    @psalm-suppress
    */
    private function extractUseStatements(ArrayIterator $tokens) : array
    {
        $extractedUseStatements = [];
        $groupedNs = '';
        $currentNs = '';
        $currentAlias = '';
        $state = 'start';
        while ($tokens->valid()) {
            $currentToken = $tokens->current();
            $tokenId = is_string($currentToken) ? $currentToken : $currentToken[0];
            $tokenValue = is_string($currentToken) ? null : $currentToken[1];
            switch ($state) {
                case 'start':
                    switch ($tokenId) {
                        case T_STRING:
                        case T_NS_SEPARATOR:
                            $currentNs .= (string) $tokenValue;
                            $currentAlias = $tokenValue;
                            break;
                        case T_NAME_QUALIFIED:
                        case T_NAME_FULLY_QUALIFIED:
                            $currentNs .= (string) $tokenValue;
                            $currentAlias = substr((string) $tokenValue, (int) strrpos((string) $tokenValue, '\\') + 1);
                            break;
                        case T_CURLY_OPEN:
                        case '{':
                            $state = 'grouped';
                            $groupedNs = $currentNs;
                            break;
                        case T_AS:
                            $state = 'start-alias';
                            break;
                        case self::T_LITERAL_USE_SEPARATOR:
                        case self::T_LITERAL_END_OF_USE:
                            $state = 'end';
                            break;
                        default:
                            break;
                    }
                    break;
                case 'start-alias':
                    switch ($tokenId) {
                        case T_STRING:
                            $currentAlias = $tokenValue;
                            break;
                        case self::T_LITERAL_USE_SEPARATOR:
                        case self::T_LITERAL_END_OF_USE:
                            $state = 'end';
                            break;
                        default:
                            break;
                    }
                    break;
                case 'grouped':
                    switch ($tokenId) {
                        case T_STRING:
                        case T_NS_SEPARATOR:
                            $currentNs .= (string) $tokenValue;
                            $currentAlias = $tokenValue;
                            break;
                        case T_AS:
                            $state = 'grouped-alias';
                            break;
                        case self::T_LITERAL_USE_SEPARATOR:
                            $state = 'grouped';
                            $extractedUseStatements[(string) $currentAlias] = $currentNs;
                            $currentNs = $groupedNs;
                            $currentAlias = '';
                            break;
                        case self::T_LITERAL_END_OF_USE:
                            $state = 'end';
                            break;
                        default:
                            break;
                    }
                    break;
                case 'grouped-alias':
                    switch ($tokenId) {
                        case T_STRING:
                            $currentAlias = $tokenValue;
                            break;
                        case self::T_LITERAL_USE_SEPARATOR:
                            $state = 'grouped';
                            $extractedUseStatements[(string) $currentAlias] = $currentNs;
                            $currentNs = $groupedNs;
                            $currentAlias = '';
                            break;
                        case self::T_LITERAL_END_OF_USE:
                            $state = 'end';
                            break;
                        default:
                            break;
                    }
            }
            if ($state === 'end') {
                break;
            }
            $tokens->next();
        }
        if ($groupedNs !== $currentNs) {
            $extractedUseStatements[(string) $currentAlias] = $currentNs;
        }
        return $extractedUseStatements;
    }
}
