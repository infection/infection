<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock;

use InvalidArgumentException;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Author;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Covers;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Generic;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Link as LinkTag;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Method;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Param;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Property;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Return_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\See as SeeTag;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Since;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Source;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Throws;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Uses;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Var_;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Version;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\FqsenResolver;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Context as TypeContext;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
use function array_merge;
use function array_slice;
use function call_user_func_array;
use function count;
use function get_class;
use function preg_match;
use function strpos;
use function trim;
final class StandardTagFactory implements TagFactory
{
    public const REGEX_TAGNAME = '[\\w\\-\\_\\\\:]+';
    private $tagHandlerMappings = ['author' => Author::class, 'covers' => Covers::class, 'deprecated' => Deprecated::class, 'link' => LinkTag::class, 'method' => Method::class, 'param' => Param::class, 'property-read' => PropertyRead::class, 'property' => Property::class, 'property-write' => PropertyWrite::class, 'return' => Return_::class, 'see' => SeeTag::class, 'since' => Since::class, 'source' => Source::class, 'throw' => Throws::class, 'throws' => Throws::class, 'uses' => Uses::class, 'var' => Var_::class, 'version' => Version::class];
    private $annotationMappings = [];
    private $tagHandlerParameterCache = [];
    private $fqsenResolver;
    private $serviceLocator = [];
    public function __construct(FqsenResolver $fqsenResolver, ?array $tagHandlers = null)
    {
        $this->fqsenResolver = $fqsenResolver;
        if ($tagHandlers !== null) {
            $this->tagHandlerMappings = $tagHandlers;
        }
        $this->addService($fqsenResolver, FqsenResolver::class);
    }
    public function create(string $tagLine, ?TypeContext $context = null) : Tag
    {
        if (!$context) {
            $context = new TypeContext('');
        }
        [$tagName, $tagBody] = $this->extractTagParts($tagLine);
        return $this->createTag(trim($tagBody), $tagName, $context);
    }
    public function addParameter(string $name, $value) : void
    {
        $this->serviceLocator[$name] = $value;
    }
    public function addService(object $service, ?string $alias = null) : void
    {
        $this->serviceLocator[$alias ?: get_class($service)] = $service;
    }
    public function registerTagHandler(string $tagName, string $handler) : void
    {
        Assert::stringNotEmpty($tagName);
        Assert::classExists($handler);
        Assert::implementsInterface($handler, Tag::class);
        if (strpos($tagName, '\\') && $tagName[0] !== '\\') {
            throw new InvalidArgumentException('A namespaced tag must have a leading backslash as it must be fully qualified');
        }
        $this->tagHandlerMappings[$tagName] = $handler;
    }
    private function extractTagParts(string $tagLine) : array
    {
        $matches = [];
        if (!preg_match('/^@(' . self::REGEX_TAGNAME . ')((?:[\\s\\(\\{])\\s*([^\\s].*)|$)/us', $tagLine, $matches)) {
            throw new InvalidArgumentException('The tag "' . $tagLine . '" does not seem to be wellformed, please check it for errors');
        }
        if (count($matches) < 3) {
            $matches[] = '';
        }
        return array_slice($matches, 1);
    }
    private function createTag(string $body, string $name, TypeContext $context) : Tag
    {
        $handlerClassName = $this->findHandlerClassName($name, $context);
        $arguments = $this->getArgumentsForParametersFromWiring($this->fetchParametersForHandlerFactoryMethod($handlerClassName), $this->getServiceLocatorWithDynamicParameters($context, $name, $body));
        try {
            $callable = [$handlerClassName, 'create'];
            Assert::isCallable($callable);
            /**
            @phpstan-var */
            $tag = call_user_func_array($callable, $arguments);
            return $tag ?? InvalidTag::create($body, $name);
        } catch (InvalidArgumentException $e) {
            return InvalidTag::create($body, $name)->withError($e);
        }
    }
    private function findHandlerClassName(string $tagName, TypeContext $context) : string
    {
        $handlerClassName = Generic::class;
        if (isset($this->tagHandlerMappings[$tagName])) {
            $handlerClassName = $this->tagHandlerMappings[$tagName];
        } elseif ($this->isAnnotation($tagName)) {
            $tagName = (string) $this->fqsenResolver->resolve($tagName, $context);
            if (isset($this->annotationMappings[$tagName])) {
                $handlerClassName = $this->annotationMappings[$tagName];
            }
        }
        return $handlerClassName;
    }
    private function getArgumentsForParametersFromWiring(array $parameters, array $locator) : array
    {
        $arguments = [];
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            $typeHint = null;
            if ($type instanceof ReflectionNamedType) {
                $typeHint = $type->getName();
                if ($typeHint === 'self') {
                    $declaringClass = $parameter->getDeclaringClass();
                    if ($declaringClass !== null) {
                        $typeHint = $declaringClass->getName();
                    }
                }
            }
            if (isset($locator[$typeHint])) {
                $arguments[] = $locator[$typeHint];
                continue;
            }
            $parameterName = $parameter->getName();
            if (isset($locator[$parameterName])) {
                $arguments[] = $locator[$parameterName];
                continue;
            }
            $arguments[] = null;
        }
        return $arguments;
    }
    private function fetchParametersForHandlerFactoryMethod(string $handlerClassName) : array
    {
        if (!isset($this->tagHandlerParameterCache[$handlerClassName])) {
            $methodReflection = new ReflectionMethod($handlerClassName, 'create');
            $this->tagHandlerParameterCache[$handlerClassName] = $methodReflection->getParameters();
        }
        return $this->tagHandlerParameterCache[$handlerClassName];
    }
    private function getServiceLocatorWithDynamicParameters(TypeContext $context, string $tagName, string $tagBody) : array
    {
        return array_merge($this->serviceLocator, ['name' => $tagName, 'body' => $tagBody, TypeContext::class => $context]);
    }
    private function isAnnotation(string $tagContent) : bool
    {
        return \false;
    }
}
