<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags;

use InvalidArgumentException;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Description;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\TypeResolver;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Context as TypeContext;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Mixed_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Void_;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use function array_keys;
use function explode;
use function implode;
use function is_string;
use function preg_match;
use function sort;
use function strpos;
use function substr;
use function trim;
use function var_export;
final class Method extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'method';
    private $methodName;
    /**
    @phpstan-var
    */
    private $arguments;
    private $isStatic;
    private $returnType;
    /**
    @phpstan-param
    */
    public function __construct(string $methodName, array $arguments = [], ?Type $returnType = null, bool $static = \false, ?Description $description = null)
    {
        Assert::stringNotEmpty($methodName);
        if ($returnType === null) {
            $returnType = new Void_();
        }
        $this->methodName = $methodName;
        $this->arguments = $this->filterArguments($arguments);
        $this->returnType = $returnType;
        $this->isStatic = $static;
        $this->description = $description;
    }
    public static function create(string $body, ?TypeResolver $typeResolver = null, ?DescriptionFactory $descriptionFactory = null, ?TypeContext $context = null) : ?self
    {
        Assert::stringNotEmpty($body);
        Assert::notNull($typeResolver);
        Assert::notNull($descriptionFactory);
        if (!preg_match('/^
                # Static keyword
                # Declares a static method ONLY if type is also present
                (?:
                    (static)
                    \\s+
                )?
                # Return type
                (?:
                    (
                        (?:[\\w\\|_\\\\]*\\$this[\\w\\|_\\\\]*)
                        |
                        (?:
                            (?:[\\w\\|_\\\\]+)
                            # array notation
                            (?:\\[\\])*
                        )*+
                    )
                    \\s+
                )?
                # Method name
                ([\\w_]+)
                # Arguments
                (?:
                    \\(([^\\)]*)\\)
                )?
                \\s*
                # Description
                (.*)
            $/sux', $body, $matches)) {
            return null;
        }
        [, $static, $returnType, $methodName, $argumentLines, $description] = $matches;
        $static = $static === 'static';
        if ($returnType === '') {
            $returnType = 'void';
        }
        $returnType = $typeResolver->resolve($returnType, $context);
        $description = $descriptionFactory->create($description, $context);
        /**
        @phpstan-var */
        $arguments = [];
        if ($argumentLines !== '') {
            $argumentsExploded = explode(',', $argumentLines);
            foreach ($argumentsExploded as $argument) {
                $argument = explode(' ', self::stripRestArg(trim($argument)), 2);
                if (strpos($argument[0], '$') === 0) {
                    $argumentName = substr($argument[0], 1);
                    $argumentType = new Mixed_();
                } else {
                    $argumentType = $typeResolver->resolve($argument[0], $context);
                    $argumentName = '';
                    if (isset($argument[1])) {
                        $argument[1] = self::stripRestArg($argument[1]);
                        $argumentName = substr($argument[1], 1);
                    }
                }
                $arguments[] = ['name' => $argumentName, 'type' => $argumentType];
            }
        }
        return new static($methodName, $arguments, $returnType, $static, $description);
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    /**
    @phpstan-return
    */
    public function getArguments() : array
    {
        return $this->arguments;
    }
    public function isStatic() : bool
    {
        return $this->isStatic;
    }
    public function getReturnType() : Type
    {
        return $this->returnType;
    }
    public function __toString() : string
    {
        $arguments = [];
        foreach ($this->arguments as $argument) {
            $arguments[] = $argument['type'] . ' $' . $argument['name'];
        }
        $argumentStr = '(' . implode(', ', $arguments) . ')';
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        $static = $this->isStatic ? 'static' : '';
        $returnType = (string) $this->returnType;
        $methodName = $this->methodName;
        return $static . ($returnType !== '' ? ($static !== '' ? ' ' : '') . $returnType : '') . ($methodName !== '' ? ($static !== '' || $returnType !== '' ? ' ' : '') . $methodName : '') . $argumentStr . ($description !== '' ? ' ' . $description : '');
    }
    /**
    @phpstan-param
    @phpstan-return
    */
    private function filterArguments(array $arguments = []) : array
    {
        $result = [];
        foreach ($arguments as $argument) {
            if (is_string($argument)) {
                $argument = ['name' => $argument];
            }
            if (!isset($argument['type'])) {
                $argument['type'] = new Mixed_();
            }
            $keys = array_keys($argument);
            sort($keys);
            if ($keys !== ['name', 'type']) {
                throw new InvalidArgumentException('Arguments can only have the "name" and "type" fields, found: ' . var_export($keys, \true));
            }
            $result[] = $argument;
        }
        return $result;
    }
    private static function stripRestArg(string $argument) : string
    {
        if (strpos($argument, '...') === 0) {
            $argument = trim(substr($argument, 3));
        }
        return $argument;
    }
}
