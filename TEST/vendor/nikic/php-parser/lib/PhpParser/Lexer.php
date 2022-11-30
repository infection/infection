<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser;

use _HumbugBox9658796bb9f0\PhpParser\Parser\Tokens;
class Lexer
{
    protected $code;
    protected $tokens;
    protected $pos;
    protected $line;
    protected $filePos;
    protected $prevCloseTagHasNewline;
    protected $tokenMap;
    protected $dropTokens;
    protected $identifierTokens;
    private $attributeStartLineUsed;
    private $attributeEndLineUsed;
    private $attributeStartTokenPosUsed;
    private $attributeEndTokenPosUsed;
    private $attributeStartFilePosUsed;
    private $attributeEndFilePosUsed;
    private $attributeCommentsUsed;
    public function __construct(array $options = [])
    {
        $this->defineCompatibilityTokens();
        $this->tokenMap = $this->createTokenMap();
        $this->identifierTokens = $this->createIdentifierTokenMap();
        $this->dropTokens = \array_fill_keys([\T_WHITESPACE, \T_OPEN_TAG, \T_COMMENT, \T_DOC_COMMENT, \T_BAD_CHARACTER], 1);
        $defaultAttributes = ['comments', 'startLine', 'endLine'];
        $usedAttributes = \array_fill_keys($options['usedAttributes'] ?? $defaultAttributes, \true);
        $this->attributeStartLineUsed = isset($usedAttributes['startLine']);
        $this->attributeEndLineUsed = isset($usedAttributes['endLine']);
        $this->attributeStartTokenPosUsed = isset($usedAttributes['startTokenPos']);
        $this->attributeEndTokenPosUsed = isset($usedAttributes['endTokenPos']);
        $this->attributeStartFilePosUsed = isset($usedAttributes['startFilePos']);
        $this->attributeEndFilePosUsed = isset($usedAttributes['endFilePos']);
        $this->attributeCommentsUsed = isset($usedAttributes['comments']);
    }
    public function startLexing(string $code, ErrorHandler $errorHandler = null)
    {
        if (null === $errorHandler) {
            $errorHandler = new ErrorHandler\Throwing();
        }
        $this->code = $code;
        $this->pos = -1;
        $this->line = 1;
        $this->filePos = 0;
        $this->prevCloseTagHasNewline = \true;
        $scream = \ini_set('xdebug.scream', '0');
        $this->tokens = @\token_get_all($code);
        $this->postprocessTokens($errorHandler);
        if (\false !== $scream) {
            \ini_set('xdebug.scream', $scream);
        }
    }
    private function handleInvalidCharacterRange($start, $end, $line, ErrorHandler $errorHandler)
    {
        $tokens = [];
        for ($i = $start; $i < $end; $i++) {
            $chr = $this->code[$i];
            if ($chr === "\x00") {
                $errorMsg = 'Unexpected null byte';
            } else {
                $errorMsg = \sprintf('Unexpected character "%s" (ASCII %d)', $chr, \ord($chr));
            }
            $tokens[] = [\T_BAD_CHARACTER, $chr, $line];
            $errorHandler->handleError(new Error($errorMsg, ['startLine' => $line, 'endLine' => $line, 'startFilePos' => $i, 'endFilePos' => $i]));
        }
        return $tokens;
    }
    private function isUnterminatedComment($token) : bool
    {
        return ($token[0] === \T_COMMENT || $token[0] === \T_DOC_COMMENT) && \substr($token[1], 0, 2) === '/*' && \substr($token[1], -2) !== '*/';
    }
    protected function postprocessTokens(ErrorHandler $errorHandler)
    {
        $filePos = 0;
        $line = 1;
        $numTokens = \count($this->tokens);
        for ($i = 0; $i < $numTokens; $i++) {
            $token = $this->tokens[$i];
            if ($token[0] === \T_BAD_CHARACTER) {
                $this->handleInvalidCharacterRange($filePos, $filePos + 1, $line, $errorHandler);
            }
            if ($token[0] === \T_COMMENT && \substr($token[1], 0, 2) !== '/*' && \preg_match('/(\\r\\n|\\n|\\r)$/D', $token[1], $matches)) {
                $trailingNewline = $matches[0];
                $token[1] = \substr($token[1], 0, -\strlen($trailingNewline));
                $this->tokens[$i] = $token;
                if (isset($this->tokens[$i + 1]) && $this->tokens[$i + 1][0] === \T_WHITESPACE) {
                    $this->tokens[$i + 1][1] = $trailingNewline . $this->tokens[$i + 1][1];
                    $this->tokens[$i + 1][2]--;
                } else {
                    \array_splice($this->tokens, $i + 1, 0, [[\T_WHITESPACE, $trailingNewline, $line]]);
                    $numTokens++;
                }
            }
            if (\is_array($token) && ($token[0] === \T_NS_SEPARATOR || isset($this->identifierTokens[$token[0]]))) {
                $lastWasSeparator = $token[0] === \T_NS_SEPARATOR;
                $text = $token[1];
                for ($j = $i + 1; isset($this->tokens[$j]); $j++) {
                    if ($lastWasSeparator) {
                        if (!isset($this->identifierTokens[$this->tokens[$j][0]])) {
                            break;
                        }
                        $lastWasSeparator = \false;
                    } else {
                        if ($this->tokens[$j][0] !== \T_NS_SEPARATOR) {
                            break;
                        }
                        $lastWasSeparator = \true;
                    }
                    $text .= $this->tokens[$j][1];
                }
                if ($lastWasSeparator) {
                    $j--;
                    $text = \substr($text, 0, -1);
                }
                if ($j > $i + 1) {
                    if ($token[0] === \T_NS_SEPARATOR) {
                        $type = \T_NAME_FULLY_QUALIFIED;
                    } else {
                        if ($token[0] === \T_NAMESPACE) {
                            $type = \T_NAME_RELATIVE;
                        } else {
                            $type = \T_NAME_QUALIFIED;
                        }
                    }
                    $token = [$type, $text, $line];
                    \array_splice($this->tokens, $i, $j - $i, [$token]);
                    $numTokens -= $j - $i - 1;
                }
            }
            if ($token === '&') {
                $next = $i + 1;
                while (isset($this->tokens[$next]) && $this->tokens[$next][0] === \T_WHITESPACE) {
                    $next++;
                }
                $followedByVarOrVarArg = isset($this->tokens[$next]) && ($this->tokens[$next][0] === \T_VARIABLE || $this->tokens[$next][0] === \T_ELLIPSIS);
                $this->tokens[$i] = $token = [$followedByVarOrVarArg ? \T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG : \T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG, '&', $line];
            }
            $tokenValue = \is_string($token) ? $token : $token[1];
            $tokenLen = \strlen($tokenValue);
            if (\substr($this->code, $filePos, $tokenLen) !== $tokenValue) {
                $nextFilePos = \strpos($this->code, $tokenValue, $filePos);
                $badCharTokens = $this->handleInvalidCharacterRange($filePos, $nextFilePos, $line, $errorHandler);
                $filePos = (int) $nextFilePos;
                \array_splice($this->tokens, $i, 0, $badCharTokens);
                $numTokens += \count($badCharTokens);
                $i += \count($badCharTokens);
            }
            $filePos += $tokenLen;
            $line += \substr_count($tokenValue, "\n");
        }
        if ($filePos !== \strlen($this->code)) {
            if (\substr($this->code, $filePos, 2) === '/*') {
                $comment = \substr($this->code, $filePos);
                $errorHandler->handleError(new Error('Unterminated comment', ['startLine' => $line, 'endLine' => $line + \substr_count($comment, "\n"), 'startFilePos' => $filePos, 'endFilePos' => $filePos + \strlen($comment)]));
                $isDocComment = isset($comment[3]) && $comment[3] === '*';
                $this->tokens[] = [$isDocComment ? \T_DOC_COMMENT : \T_COMMENT, $comment, $line];
            } else {
                $badCharTokens = $this->handleInvalidCharacterRange($filePos, \strlen($this->code), $line, $errorHandler);
                $this->tokens = \array_merge($this->tokens, $badCharTokens);
            }
            return;
        }
        if (\count($this->tokens) > 0) {
            $lastToken = $this->tokens[\count($this->tokens) - 1];
            if ($this->isUnterminatedComment($lastToken)) {
                $errorHandler->handleError(new Error('Unterminated comment', ['startLine' => $line - \substr_count($lastToken[1], "\n"), 'endLine' => $line, 'startFilePos' => $filePos - \strlen($lastToken[1]), 'endFilePos' => $filePos]));
            }
        }
    }
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null) : int
    {
        $startAttributes = [];
        $endAttributes = [];
        while (1) {
            if (isset($this->tokens[++$this->pos])) {
                $token = $this->tokens[$this->pos];
            } else {
                $token = "\x00";
            }
            if ($this->attributeStartLineUsed) {
                $startAttributes['startLine'] = $this->line;
            }
            if ($this->attributeStartTokenPosUsed) {
                $startAttributes['startTokenPos'] = $this->pos;
            }
            if ($this->attributeStartFilePosUsed) {
                $startAttributes['startFilePos'] = $this->filePos;
            }
            if (\is_string($token)) {
                $value = $token;
                if (isset($token[1])) {
                    $this->filePos += 2;
                    $id = \ord('"');
                } else {
                    $this->filePos += 1;
                    $id = \ord($token);
                }
            } elseif (!isset($this->dropTokens[$token[0]])) {
                $value = $token[1];
                $id = $this->tokenMap[$token[0]];
                if (\T_CLOSE_TAG === $token[0]) {
                    $this->prevCloseTagHasNewline = \false !== \strpos($token[1], "\n") || \false !== \strpos($token[1], "\r");
                } elseif (\T_INLINE_HTML === $token[0]) {
                    $startAttributes['hasLeadingNewline'] = $this->prevCloseTagHasNewline;
                }
                $this->line += \substr_count($value, "\n");
                $this->filePos += \strlen($value);
            } else {
                $origLine = $this->line;
                $origFilePos = $this->filePos;
                $this->line += \substr_count($token[1], "\n");
                $this->filePos += \strlen($token[1]);
                if (\T_COMMENT === $token[0] || \T_DOC_COMMENT === $token[0]) {
                    if ($this->attributeCommentsUsed) {
                        $comment = \T_DOC_COMMENT === $token[0] ? new Comment\Doc($token[1], $origLine, $origFilePos, $this->pos, $this->line, $this->filePos - 1, $this->pos) : new Comment($token[1], $origLine, $origFilePos, $this->pos, $this->line, $this->filePos - 1, $this->pos);
                        $startAttributes['comments'][] = $comment;
                    }
                }
                continue;
            }
            if ($this->attributeEndLineUsed) {
                $endAttributes['endLine'] = $this->line;
            }
            if ($this->attributeEndTokenPosUsed) {
                $endAttributes['endTokenPos'] = $this->pos;
            }
            if ($this->attributeEndFilePosUsed) {
                $endAttributes['endFilePos'] = $this->filePos - 1;
            }
            return $id;
        }
        throw new \RuntimeException('Reached end of lexer loop');
    }
    public function getTokens() : array
    {
        return $this->tokens;
    }
    public function handleHaltCompiler() : string
    {
        $textAfter = \substr($this->code, $this->filePos);
        if (!\preg_match('~^\\s*\\(\\s*\\)\\s*(?:;|\\?>\\r?\\n?)~', $textAfter, $matches)) {
            throw new Error('__HALT_COMPILER must be followed by "();"');
        }
        $this->pos = \count($this->tokens);
        return \substr($textAfter, \strlen($matches[0]));
    }
    private function defineCompatibilityTokens()
    {
        static $compatTokensDefined = \false;
        if ($compatTokensDefined) {
            return;
        }
        $compatTokens = ['T_BAD_CHARACTER', 'T_FN', 'T_COALESCE_EQUAL', 'T_NAME_QUALIFIED', 'T_NAME_FULLY_QUALIFIED', 'T_NAME_RELATIVE', 'T_MATCH', 'T_NULLSAFE_OBJECT_OPERATOR', 'T_ATTRIBUTE', 'T_ENUM', 'T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG', 'T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG', 'T_READONLY'];
        $usedTokenIds = [];
        foreach ($compatTokens as $token) {
            if (\defined($token)) {
                $tokenId = \constant($token);
                $clashingToken = $usedTokenIds[$tokenId] ?? null;
                if ($clashingToken !== null) {
                    throw new \Error(\sprintf('Token %s has same ID as token %s, ' . 'you may be using a library with broken token emulation', $token, $clashingToken));
                }
                $usedTokenIds[$tokenId] = $token;
            }
        }
        $newTokenId = -1;
        foreach ($compatTokens as $token) {
            if (!\defined($token)) {
                while (isset($usedTokenIds[$newTokenId])) {
                    $newTokenId--;
                }
                \define($token, $newTokenId);
                $newTokenId--;
            }
        }
        $compatTokensDefined = \true;
    }
    protected function createTokenMap() : array
    {
        $tokenMap = [];
        for ($i = 256; $i < 1000; ++$i) {
            if (\T_DOUBLE_COLON === $i) {
                $tokenMap[$i] = Tokens::T_PAAMAYIM_NEKUDOTAYIM;
            } elseif (\T_OPEN_TAG_WITH_ECHO === $i) {
                $tokenMap[$i] = Tokens::T_ECHO;
            } elseif (\T_CLOSE_TAG === $i) {
                $tokenMap[$i] = \ord(';');
            } elseif ('UNKNOWN' !== ($name = \token_name($i))) {
                if ('T_HASHBANG' === $name) {
                    $tokenMap[$i] = Tokens::T_INLINE_HTML;
                } elseif (\defined($name = Tokens::class . '::' . $name)) {
                    $tokenMap[$i] = \constant($name);
                }
            }
        }
        if (\defined('T_ONUMBER')) {
            $tokenMap[\T_ONUMBER] = Tokens::T_DNUMBER;
        }
        if (\defined('T_COMPILER_HALT_OFFSET')) {
            $tokenMap[\T_COMPILER_HALT_OFFSET] = Tokens::T_STRING;
        }
        $tokenMap[\T_FN] = Tokens::T_FN;
        $tokenMap[\T_COALESCE_EQUAL] = Tokens::T_COALESCE_EQUAL;
        $tokenMap[\T_NAME_QUALIFIED] = Tokens::T_NAME_QUALIFIED;
        $tokenMap[\T_NAME_FULLY_QUALIFIED] = Tokens::T_NAME_FULLY_QUALIFIED;
        $tokenMap[\T_NAME_RELATIVE] = Tokens::T_NAME_RELATIVE;
        $tokenMap[\T_MATCH] = Tokens::T_MATCH;
        $tokenMap[\T_NULLSAFE_OBJECT_OPERATOR] = Tokens::T_NULLSAFE_OBJECT_OPERATOR;
        $tokenMap[\T_ATTRIBUTE] = Tokens::T_ATTRIBUTE;
        $tokenMap[\T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG] = Tokens::T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG;
        $tokenMap[\T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG] = Tokens::T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG;
        $tokenMap[\T_ENUM] = Tokens::T_ENUM;
        $tokenMap[\T_READONLY] = Tokens::T_READONLY;
        return $tokenMap;
    }
    private function createIdentifierTokenMap() : array
    {
        return \array_fill_keys([\T_STRING, \T_STATIC, \T_ABSTRACT, \T_FINAL, \T_PRIVATE, \T_PROTECTED, \T_PUBLIC, \T_READONLY, \T_INCLUDE, \T_INCLUDE_ONCE, \T_EVAL, \T_REQUIRE, \T_REQUIRE_ONCE, \T_LOGICAL_OR, \T_LOGICAL_XOR, \T_LOGICAL_AND, \T_INSTANCEOF, \T_NEW, \T_CLONE, \T_EXIT, \T_IF, \T_ELSEIF, \T_ELSE, \T_ENDIF, \T_ECHO, \T_DO, \T_WHILE, \T_ENDWHILE, \T_FOR, \T_ENDFOR, \T_FOREACH, \T_ENDFOREACH, \T_DECLARE, \T_ENDDECLARE, \T_AS, \T_TRY, \T_CATCH, \T_FINALLY, \T_THROW, \T_USE, \T_INSTEADOF, \T_GLOBAL, \T_VAR, \T_UNSET, \T_ISSET, \T_EMPTY, \T_CONTINUE, \T_GOTO, \T_FUNCTION, \T_CONST, \T_RETURN, \T_PRINT, \T_YIELD, \T_LIST, \T_SWITCH, \T_ENDSWITCH, \T_CASE, \T_DEFAULT, \T_BREAK, \T_ARRAY, \T_CALLABLE, \T_EXTENDS, \T_IMPLEMENTS, \T_NAMESPACE, \T_TRAIT, \T_INTERFACE, \T_CLASS, \T_CLASS_C, \T_TRAIT_C, \T_FUNC_C, \T_METHOD_C, \T_LINE, \T_FILE, \T_DIR, \T_NS_C, \T_HALT_COMPILER, \T_FN, \T_MATCH], \true);
    }
}
