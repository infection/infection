<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser;

use _HumbugBoxb47773b41c19\PhpParser\Node\Expr;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr\Cast\Double;
use _HumbugBoxb47773b41c19\PhpParser\Node\Name;
use _HumbugBoxb47773b41c19\PhpParser\Node\Param;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar\Encapsed;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar\LNumber;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar\String_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Class_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\ClassConst;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\ClassMethod;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Enum_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Interface_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Namespace_;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\Property;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\TryCatch;
use _HumbugBoxb47773b41c19\PhpParser\Node\Stmt\UseUse;
use _HumbugBoxb47773b41c19\PhpParser\Node\VarLikeIdentifier;
abstract class ParserAbstract implements Parser
{
    const SYMBOL_NONE = -1;
    protected $tokenToSymbolMapSize;
    protected $actionTableSize;
    protected $gotoTableSize;
    protected $invalidSymbol;
    protected $errorSymbol;
    protected $defaultAction;
    protected $unexpectedTokenRule;
    protected $YY2TBLSTATE;
    protected $numNonLeafStates;
    protected $tokenToSymbol;
    protected $symbolToName;
    protected $productions;
    protected $actionBase;
    protected $action;
    protected $actionCheck;
    protected $actionDefault;
    protected $reduceCallbacks;
    protected $gotoBase;
    protected $goto;
    protected $gotoCheck;
    protected $gotoDefault;
    protected $ruleToNonTerminal;
    protected $ruleToLength;
    protected $lexer;
    protected $semValue;
    protected $semStack;
    protected $startAttributeStack;
    protected $endAttributeStack;
    protected $endAttributes;
    protected $lookaheadStartAttributes;
    protected $errorHandler;
    protected $errorState;
    protected abstract function initReduceCallbacks();
    public function __construct(Lexer $lexer, array $options = [])
    {
        $this->lexer = $lexer;
        if (isset($options['throwOnError'])) {
            throw new \LogicException('"throwOnError" is no longer supported, use "errorHandler" instead');
        }
        $this->initReduceCallbacks();
    }
    public function parse(string $code, ErrorHandler $errorHandler = null)
    {
        $this->errorHandler = $errorHandler ?: new ErrorHandler\Throwing();
        $this->lexer->startLexing($code, $this->errorHandler);
        $result = $this->doParse();
        $this->startAttributeStack = [];
        $this->endAttributeStack = [];
        $this->semStack = [];
        $this->semValue = null;
        return $result;
    }
    protected function doParse()
    {
        $symbol = self::SYMBOL_NONE;
        $startAttributes = [];
        $endAttributes = [];
        $this->endAttributes = $endAttributes;
        $this->startAttributeStack = [];
        $this->endAttributeStack = [$endAttributes];
        $state = 0;
        $stateStack = [$state];
        $this->semStack = [];
        $stackPos = 0;
        $this->errorState = 0;
        for (;;) {
            if ($this->actionBase[$state] === 0) {
                $rule = $this->actionDefault[$state];
            } else {
                if ($symbol === self::SYMBOL_NONE) {
                    $tokenId = $this->lexer->getNextToken($tokenValue, $startAttributes, $endAttributes);
                    $symbol = $tokenId >= 0 && $tokenId < $this->tokenToSymbolMapSize ? $this->tokenToSymbol[$tokenId] : $this->invalidSymbol;
                    if ($symbol === $this->invalidSymbol) {
                        throw new \RangeException(\sprintf('The lexer returned an invalid token (id=%d, value=%s)', $tokenId, $tokenValue));
                    }
                    $this->lookaheadStartAttributes = $startAttributes;
                }
                $idx = $this->actionBase[$state] + $symbol;
                if (($idx >= 0 && $idx < $this->actionTableSize && $this->actionCheck[$idx] === $symbol || $state < $this->YY2TBLSTATE && ($idx = $this->actionBase[$state + $this->numNonLeafStates] + $symbol) >= 0 && $idx < $this->actionTableSize && $this->actionCheck[$idx] === $symbol) && ($action = $this->action[$idx]) !== $this->defaultAction) {
                    if ($action > 0) {
                        ++$stackPos;
                        $stateStack[$stackPos] = $state = $action;
                        $this->semStack[$stackPos] = $tokenValue;
                        $this->startAttributeStack[$stackPos] = $startAttributes;
                        $this->endAttributeStack[$stackPos] = $endAttributes;
                        $this->endAttributes = $endAttributes;
                        $symbol = self::SYMBOL_NONE;
                        if ($this->errorState) {
                            --$this->errorState;
                        }
                        if ($action < $this->numNonLeafStates) {
                            continue;
                        }
                        $rule = $action - $this->numNonLeafStates;
                    } else {
                        $rule = -$action;
                    }
                } else {
                    $rule = $this->actionDefault[$state];
                }
            }
            for (;;) {
                if ($rule === 0) {
                    return $this->semValue;
                } elseif ($rule !== $this->unexpectedTokenRule) {
                    try {
                        $this->reduceCallbacks[$rule]($stackPos);
                    } catch (Error $e) {
                        if (-1 === $e->getStartLine() && isset($startAttributes['startLine'])) {
                            $e->setStartLine($startAttributes['startLine']);
                        }
                        $this->emitError($e);
                        return null;
                    }
                    $lastEndAttributes = $this->endAttributeStack[$stackPos];
                    $ruleLength = $this->ruleToLength[$rule];
                    $stackPos -= $ruleLength;
                    $nonTerminal = $this->ruleToNonTerminal[$rule];
                    $idx = $this->gotoBase[$nonTerminal] + $stateStack[$stackPos];
                    if ($idx >= 0 && $idx < $this->gotoTableSize && $this->gotoCheck[$idx] === $nonTerminal) {
                        $state = $this->goto[$idx];
                    } else {
                        $state = $this->gotoDefault[$nonTerminal];
                    }
                    ++$stackPos;
                    $stateStack[$stackPos] = $state;
                    $this->semStack[$stackPos] = $this->semValue;
                    $this->endAttributeStack[$stackPos] = $lastEndAttributes;
                    if ($ruleLength === 0) {
                        $this->startAttributeStack[$stackPos] = $this->lookaheadStartAttributes;
                    }
                } else {
                    switch ($this->errorState) {
                        case 0:
                            $msg = $this->getErrorMessage($symbol, $state);
                            $this->emitError(new Error($msg, $startAttributes + $endAttributes));
                        case 1:
                        case 2:
                            $this->errorState = 3;
                            while (!(($idx = $this->actionBase[$state] + $this->errorSymbol) >= 0 && $idx < $this->actionTableSize && $this->actionCheck[$idx] === $this->errorSymbol || $state < $this->YY2TBLSTATE && ($idx = $this->actionBase[$state + $this->numNonLeafStates] + $this->errorSymbol) >= 0 && $idx < $this->actionTableSize && $this->actionCheck[$idx] === $this->errorSymbol) || ($action = $this->action[$idx]) === $this->defaultAction) {
                                if ($stackPos <= 0) {
                                    return null;
                                }
                                $state = $stateStack[--$stackPos];
                            }
                            ++$stackPos;
                            $stateStack[$stackPos] = $state = $action;
                            $this->startAttributeStack[$stackPos] = $this->lookaheadStartAttributes;
                            $this->endAttributeStack[$stackPos] = $this->endAttributeStack[$stackPos - 1];
                            $this->endAttributes = $this->endAttributeStack[$stackPos - 1];
                            break;
                        case 3:
                            if ($symbol === 0) {
                                return null;
                            }
                            $symbol = self::SYMBOL_NONE;
                            break 2;
                    }
                }
                if ($state < $this->numNonLeafStates) {
                    break;
                }
                $rule = $state - $this->numNonLeafStates;
            }
        }
        throw new \RuntimeException('Reached end of parser loop');
    }
    protected function emitError(Error $error)
    {
        $this->errorHandler->handleError($error);
    }
    protected function getErrorMessage(int $symbol, int $state) : string
    {
        $expectedString = '';
        if ($expected = $this->getExpectedTokens($state)) {
            $expectedString = ', expecting ' . \implode(' or ', $expected);
        }
        return 'Syntax error, unexpected ' . $this->symbolToName[$symbol] . $expectedString;
    }
    protected function getExpectedTokens(int $state) : array
    {
        $expected = [];
        $base = $this->actionBase[$state];
        foreach ($this->symbolToName as $symbol => $name) {
            $idx = $base + $symbol;
            if ($idx >= 0 && $idx < $this->actionTableSize && $this->actionCheck[$idx] === $symbol || $state < $this->YY2TBLSTATE && ($idx = $this->actionBase[$state + $this->numNonLeafStates] + $symbol) >= 0 && $idx < $this->actionTableSize && $this->actionCheck[$idx] === $symbol) {
                if ($this->action[$idx] !== $this->unexpectedTokenRule && $this->action[$idx] !== $this->defaultAction && $symbol !== $this->errorSymbol) {
                    if (\count($expected) === 4) {
                        return [];
                    }
                    $expected[] = $name;
                }
            }
        }
        return $expected;
    }
    protected function handleNamespaces(array $stmts) : array
    {
        $hasErrored = \false;
        $style = $this->getNamespacingStyle($stmts);
        if (null === $style) {
            return $stmts;
        } elseif ('brace' === $style) {
            $afterFirstNamespace = \false;
            foreach ($stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\Namespace_) {
                    $afterFirstNamespace = \true;
                } elseif (!$stmt instanceof Node\Stmt\HaltCompiler && !$stmt instanceof Node\Stmt\Nop && $afterFirstNamespace && !$hasErrored) {
                    $this->emitError(new Error('No code may exist outside of namespace {}', $stmt->getAttributes()));
                    $hasErrored = \true;
                }
            }
            return $stmts;
        } else {
            $resultStmts = [];
            $targetStmts =& $resultStmts;
            $lastNs = null;
            foreach ($stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\Namespace_) {
                    if ($lastNs !== null) {
                        $this->fixupNamespaceAttributes($lastNs);
                    }
                    if ($stmt->stmts === null) {
                        $stmt->stmts = [];
                        $targetStmts =& $stmt->stmts;
                        $resultStmts[] = $stmt;
                    } else {
                        $resultStmts[] = $stmt;
                        $targetStmts =& $resultStmts;
                    }
                    $lastNs = $stmt;
                } elseif ($stmt instanceof Node\Stmt\HaltCompiler) {
                    $resultStmts[] = $stmt;
                } else {
                    $targetStmts[] = $stmt;
                }
            }
            if ($lastNs !== null) {
                $this->fixupNamespaceAttributes($lastNs);
            }
            return $resultStmts;
        }
    }
    private function fixupNamespaceAttributes(Node\Stmt\Namespace_ $stmt)
    {
        if (empty($stmt->stmts)) {
            return;
        }
        $endAttributes = ['endLine', 'endFilePos', 'endTokenPos'];
        $lastStmt = $stmt->stmts[\count($stmt->stmts) - 1];
        foreach ($endAttributes as $endAttribute) {
            if ($lastStmt->hasAttribute($endAttribute)) {
                $stmt->setAttribute($endAttribute, $lastStmt->getAttribute($endAttribute));
            }
        }
    }
    private function getNamespacingStyle(array $stmts)
    {
        $style = null;
        $hasNotAllowedStmts = \false;
        foreach ($stmts as $i => $stmt) {
            if ($stmt instanceof Node\Stmt\Namespace_) {
                $currentStyle = null === $stmt->stmts ? 'semicolon' : 'brace';
                if (null === $style) {
                    $style = $currentStyle;
                    if ($hasNotAllowedStmts) {
                        $this->emitError(new Error('Namespace declaration statement has to be the very first statement in the script', $stmt->getLine()));
                    }
                } elseif ($style !== $currentStyle) {
                    $this->emitError(new Error('Cannot mix bracketed namespace declarations with unbracketed namespace declarations', $stmt->getLine()));
                    return 'semicolon';
                }
                continue;
            }
            if ($stmt instanceof Node\Stmt\Declare_ || $stmt instanceof Node\Stmt\HaltCompiler || $stmt instanceof Node\Stmt\Nop) {
                continue;
            }
            if ($i === 0 && $stmt instanceof Node\Stmt\InlineHTML && \preg_match('/\\A#!.*\\r?\\n\\z/', $stmt->value)) {
                continue;
            }
            $hasNotAllowedStmts = \true;
        }
        return $style;
    }
    protected function fixupPhp5StaticPropCall($prop, array $args, array $attributes) : Expr\StaticCall
    {
        if ($prop instanceof Node\Expr\StaticPropertyFetch) {
            $name = $prop->name instanceof VarLikeIdentifier ? $prop->name->toString() : $prop->name;
            $var = new Expr\Variable($name, $prop->name->getAttributes());
            return new Expr\StaticCall($prop->class, $var, $args, $attributes);
        } elseif ($prop instanceof Node\Expr\ArrayDimFetch) {
            $tmp = $prop;
            while ($tmp->var instanceof Node\Expr\ArrayDimFetch) {
                $tmp = $tmp->var;
            }
            $staticProp = $tmp->var;
            $tmp = $prop;
            $this->fixupStartAttributes($tmp, $staticProp->name);
            while ($tmp->var instanceof Node\Expr\ArrayDimFetch) {
                $tmp = $tmp->var;
                $this->fixupStartAttributes($tmp, $staticProp->name);
            }
            $name = $staticProp->name instanceof VarLikeIdentifier ? $staticProp->name->toString() : $staticProp->name;
            $tmp->var = new Expr\Variable($name, $staticProp->name->getAttributes());
            return new Expr\StaticCall($staticProp->class, $prop, $args, $attributes);
        } else {
            throw new \Exception();
        }
    }
    protected function fixupStartAttributes(Node $to, Node $from)
    {
        $startAttributes = ['startLine', 'startFilePos', 'startTokenPos'];
        foreach ($startAttributes as $startAttribute) {
            if ($from->hasAttribute($startAttribute)) {
                $to->setAttribute($startAttribute, $from->getAttribute($startAttribute));
            }
        }
    }
    protected function handleBuiltinTypes(Name $name)
    {
        $builtinTypes = ['bool' => \true, 'int' => \true, 'float' => \true, 'string' => \true, 'iterable' => \true, 'void' => \true, 'object' => \true, 'null' => \true, 'false' => \true, 'mixed' => \true, 'never' => \true, 'true' => \true];
        if (!$name->isUnqualified()) {
            return $name;
        }
        $lowerName = $name->toLowerString();
        if (!isset($builtinTypes[$lowerName])) {
            return $name;
        }
        return new Node\Identifier($lowerName, $name->getAttributes());
    }
    protected function getAttributesAt(int $pos) : array
    {
        return $this->startAttributeStack[$pos] + $this->endAttributeStack[$pos];
    }
    protected function getFloatCastKind(string $cast) : int
    {
        $cast = \strtolower($cast);
        if (\strpos($cast, 'float') !== \false) {
            return Double::KIND_FLOAT;
        }
        if (\strpos($cast, 'real') !== \false) {
            return Double::KIND_REAL;
        }
        return Double::KIND_DOUBLE;
    }
    protected function parseLNumber($str, $attributes, $allowInvalidOctal = \false)
    {
        try {
            return LNumber::fromString($str, $attributes, $allowInvalidOctal);
        } catch (Error $error) {
            $this->emitError($error);
            return new LNumber(0, $attributes);
        }
    }
    protected function parseNumString(string $str, array $attributes)
    {
        if (!\preg_match('/^(?:0|-?[1-9][0-9]*)$/', $str)) {
            return new String_($str, $attributes);
        }
        $num = +$str;
        if (!\is_int($num)) {
            return new String_($str, $attributes);
        }
        return new LNumber($num, $attributes);
    }
    protected function stripIndentation(string $string, int $indentLen, string $indentChar, bool $newlineAtStart, bool $newlineAtEnd, array $attributes)
    {
        if ($indentLen === 0) {
            return $string;
        }
        $start = $newlineAtStart ? '(?:(?<=\\n)|\\A)' : '(?<=\\n)';
        $end = $newlineAtEnd ? '(?:(?=[\\r\\n])|\\z)' : '(?=[\\r\\n])';
        $regex = '/' . $start . '([ \\t]*)(' . $end . ')?/';
        return \preg_replace_callback($regex, function ($matches) use($indentLen, $indentChar, $attributes) {
            $prefix = \substr($matches[1], 0, $indentLen);
            if (\false !== \strpos($prefix, $indentChar === " " ? "\t" : " ")) {
                $this->emitError(new Error('Invalid indentation - tabs and spaces cannot be mixed', $attributes));
            } elseif (\strlen($prefix) < $indentLen && !isset($matches[2])) {
                $this->emitError(new Error('Invalid body indentation level ' . '(expecting an indentation level of at least ' . $indentLen . ')', $attributes));
            }
            return \substr($matches[0], \strlen($prefix));
        }, $string);
    }
    protected function parseDocString(string $startToken, $contents, string $endToken, array $attributes, array $endTokenAttributes, bool $parseUnicodeEscape)
    {
        $kind = \strpos($startToken, "'") === \false ? String_::KIND_HEREDOC : String_::KIND_NOWDOC;
        $regex = '/\\A[bB]?<<<[ \\t]*[\'"]?([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)[\'"]?(?:\\r\\n|\\n|\\r)\\z/';
        $result = \preg_match($regex, $startToken, $matches);
        \assert($result === 1);
        $label = $matches[1];
        $result = \preg_match('/\\A[ \\t]*/', $endToken, $matches);
        \assert($result === 1);
        $indentation = $matches[0];
        $attributes['kind'] = $kind;
        $attributes['docLabel'] = $label;
        $attributes['docIndentation'] = $indentation;
        $indentHasSpaces = \false !== \strpos($indentation, " ");
        $indentHasTabs = \false !== \strpos($indentation, "\t");
        if ($indentHasSpaces && $indentHasTabs) {
            $this->emitError(new Error('Invalid indentation - tabs and spaces cannot be mixed', $endTokenAttributes));
            $indentation = '';
        }
        $indentLen = \strlen($indentation);
        $indentChar = $indentHasSpaces ? " " : "\t";
        if (\is_string($contents)) {
            if ($contents === '') {
                return new String_('', $attributes);
            }
            $contents = $this->stripIndentation($contents, $indentLen, $indentChar, \true, \true, $attributes);
            $contents = \preg_replace('~(\\r\\n|\\n|\\r)\\z~', '', $contents);
            if ($kind === String_::KIND_HEREDOC) {
                $contents = String_::parseEscapeSequences($contents, null, $parseUnicodeEscape);
            }
            return new String_($contents, $attributes);
        } else {
            \assert(\count($contents) > 0);
            if (!$contents[0] instanceof Node\Scalar\EncapsedStringPart) {
                $this->stripIndentation('', $indentLen, $indentChar, \true, \false, $contents[0]->getAttributes());
            }
            $newContents = [];
            foreach ($contents as $i => $part) {
                if ($part instanceof Node\Scalar\EncapsedStringPart) {
                    $isLast = $i === \count($contents) - 1;
                    $part->value = $this->stripIndentation($part->value, $indentLen, $indentChar, $i === 0, $isLast, $part->getAttributes());
                    $part->value = String_::parseEscapeSequences($part->value, null, $parseUnicodeEscape);
                    if ($isLast) {
                        $part->value = \preg_replace('~(\\r\\n|\\n|\\r)\\z~', '', $part->value);
                    }
                    if ('' === $part->value) {
                        continue;
                    }
                }
                $newContents[] = $part;
            }
            return new Encapsed($newContents, $attributes);
        }
    }
    protected function createCommentNopAttributes(array $comments)
    {
        $comment = $comments[\count($comments) - 1];
        $commentEndLine = $comment->getEndLine();
        $commentEndFilePos = $comment->getEndFilePos();
        $commentEndTokenPos = $comment->getEndTokenPos();
        $attributes = ['comments' => $comments];
        if (-1 !== $commentEndLine) {
            $attributes['startLine'] = $commentEndLine;
            $attributes['endLine'] = $commentEndLine;
        }
        if (-1 !== $commentEndFilePos) {
            $attributes['startFilePos'] = $commentEndFilePos + 1;
            $attributes['endFilePos'] = $commentEndFilePos;
        }
        if (-1 !== $commentEndTokenPos) {
            $attributes['startTokenPos'] = $commentEndTokenPos + 1;
            $attributes['endTokenPos'] = $commentEndTokenPos;
        }
        return $attributes;
    }
    protected function checkClassModifier($a, $b, $modifierPos)
    {
        try {
            Class_::verifyClassModifier($a, $b);
        } catch (Error $error) {
            $error->setAttributes($this->getAttributesAt($modifierPos));
            $this->emitError($error);
        }
    }
    protected function checkModifier($a, $b, $modifierPos)
    {
        try {
            Class_::verifyModifier($a, $b);
        } catch (Error $error) {
            $error->setAttributes($this->getAttributesAt($modifierPos));
            $this->emitError($error);
        }
    }
    protected function checkParam(Param $node)
    {
        if ($node->variadic && null !== $node->default) {
            $this->emitError(new Error('Variadic parameter cannot have a default value', $node->default->getAttributes()));
        }
    }
    protected function checkTryCatch(TryCatch $node)
    {
        if (empty($node->catches) && null === $node->finally) {
            $this->emitError(new Error('Cannot use try without catch or finally', $node->getAttributes()));
        }
    }
    protected function checkNamespace(Namespace_ $node)
    {
        if (null !== $node->stmts) {
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Namespace_) {
                    $this->emitError(new Error('Namespace declarations cannot be nested', $stmt->getAttributes()));
                }
            }
        }
    }
    private function checkClassName($name, $namePos)
    {
        if (null !== $name && $name->isSpecialClassName()) {
            $this->emitError(new Error(\sprintf('Cannot use \'%s\' as class name as it is reserved', $name), $this->getAttributesAt($namePos)));
        }
    }
    private function checkImplementedInterfaces(array $interfaces)
    {
        foreach ($interfaces as $interface) {
            if ($interface->isSpecialClassName()) {
                $this->emitError(new Error(\sprintf('Cannot use \'%s\' as interface name as it is reserved', $interface), $interface->getAttributes()));
            }
        }
    }
    protected function checkClass(Class_ $node, $namePos)
    {
        $this->checkClassName($node->name, $namePos);
        if ($node->extends && $node->extends->isSpecialClassName()) {
            $this->emitError(new Error(\sprintf('Cannot use \'%s\' as class name as it is reserved', $node->extends), $node->extends->getAttributes()));
        }
        $this->checkImplementedInterfaces($node->implements);
    }
    protected function checkInterface(Interface_ $node, $namePos)
    {
        $this->checkClassName($node->name, $namePos);
        $this->checkImplementedInterfaces($node->extends);
    }
    protected function checkEnum(Enum_ $node, $namePos)
    {
        $this->checkClassName($node->name, $namePos);
        $this->checkImplementedInterfaces($node->implements);
    }
    protected function checkClassMethod(ClassMethod $node, $modifierPos)
    {
        if ($node->flags & Class_::MODIFIER_STATIC) {
            switch ($node->name->toLowerString()) {
                case '__construct':
                    $this->emitError(new Error(\sprintf('Constructor %s() cannot be static', $node->name), $this->getAttributesAt($modifierPos)));
                    break;
                case '__destruct':
                    $this->emitError(new Error(\sprintf('Destructor %s() cannot be static', $node->name), $this->getAttributesAt($modifierPos)));
                    break;
                case '__clone':
                    $this->emitError(new Error(\sprintf('Clone method %s() cannot be static', $node->name), $this->getAttributesAt($modifierPos)));
                    break;
            }
        }
        if ($node->flags & Class_::MODIFIER_READONLY) {
            $this->emitError(new Error(\sprintf('Method %s() cannot be readonly', $node->name), $this->getAttributesAt($modifierPos)));
        }
    }
    protected function checkClassConst(ClassConst $node, $modifierPos)
    {
        if ($node->flags & Class_::MODIFIER_STATIC) {
            $this->emitError(new Error("Cannot use 'static' as constant modifier", $this->getAttributesAt($modifierPos)));
        }
        if ($node->flags & Class_::MODIFIER_ABSTRACT) {
            $this->emitError(new Error("Cannot use 'abstract' as constant modifier", $this->getAttributesAt($modifierPos)));
        }
        if ($node->flags & Class_::MODIFIER_READONLY) {
            $this->emitError(new Error("Cannot use 'readonly' as constant modifier", $this->getAttributesAt($modifierPos)));
        }
    }
    protected function checkProperty(Property $node, $modifierPos)
    {
        if ($node->flags & Class_::MODIFIER_ABSTRACT) {
            $this->emitError(new Error('Properties cannot be declared abstract', $this->getAttributesAt($modifierPos)));
        }
        if ($node->flags & Class_::MODIFIER_FINAL) {
            $this->emitError(new Error('Properties cannot be declared final', $this->getAttributesAt($modifierPos)));
        }
    }
    protected function checkUseUse(UseUse $node, $namePos)
    {
        if ($node->alias && $node->alias->isSpecialClassName()) {
            $this->emitError(new Error(\sprintf('Cannot use %s as %s because \'%2$s\' is a special class name', $node->name, $node->alias), $this->getAttributesAt($namePos)));
        }
    }
}
