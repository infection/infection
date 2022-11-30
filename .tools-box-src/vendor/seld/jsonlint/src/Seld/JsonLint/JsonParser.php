<?php

namespace _HumbugBoxb47773b41c19\Seld\JsonLint;

use stdClass;
class JsonParser
{
    const DETECT_KEY_CONFLICTS = 1;
    const ALLOW_DUPLICATE_KEYS = 2;
    const PARSE_TO_ASSOC = 4;
    private $lexer;
    /**
    @psalm-var
    */
    private $flags;
    private $stack;
    private $vstack;
    private $lstack;
    /**
    @phpstan-var
    */
    private $symbols = array('error' => 2, 'JSONString' => 3, 'STRING' => 4, 'JSONNumber' => 5, 'NUMBER' => 6, 'JSONNullLiteral' => 7, 'NULL' => 8, 'JSONBooleanLiteral' => 9, 'TRUE' => 10, 'FALSE' => 11, 'JSONText' => 12, 'JSONValue' => 13, 'EOF' => 14, 'JSONObject' => 15, 'JSONArray' => 16, '{' => 17, '}' => 18, 'JSONMemberList' => 19, 'JSONMember' => 20, ':' => 21, ',' => 22, '[' => 23, ']' => 24, 'JSONElementList' => 25, '$accept' => 0, '$end' => 1);
    /**
    @phpstan-var
    @const
    */
    private $terminals_ = array(2 => "error", 4 => "STRING", 6 => "NUMBER", 8 => "NULL", 10 => "TRUE", 11 => "FALSE", 14 => "EOF", 17 => "{", 18 => "}", 21 => ":", 22 => ",", 23 => "[", 24 => "]");
    /**
    @phpstan-var
    @const
    */
    private $productions_ = array(1 => array(3, 1), 2 => array(5, 1), 3 => array(7, 1), 4 => array(9, 1), 5 => array(9, 1), 6 => array(12, 2), 7 => array(13, 1), 8 => array(13, 1), 9 => array(13, 1), 10 => array(13, 1), 11 => array(13, 1), 12 => array(13, 1), 13 => array(15, 2), 14 => array(15, 3), 15 => array(20, 3), 16 => array(19, 1), 17 => array(19, 3), 18 => array(16, 2), 19 => array(16, 3), 20 => array(25, 1), 21 => array(25, 3));
    /**
    @const
    */
    private $table = array(0 => array(3 => 5, 4 => array(1, 12), 5 => 6, 6 => array(1, 13), 7 => 3, 8 => array(1, 9), 9 => 4, 10 => array(1, 10), 11 => array(1, 11), 12 => 1, 13 => 2, 15 => 7, 16 => 8, 17 => array(1, 14), 23 => array(1, 15)), 1 => array(1 => array(3)), 2 => array(14 => array(1, 16)), 3 => array(14 => array(2, 7), 18 => array(2, 7), 22 => array(2, 7), 24 => array(2, 7)), 4 => array(14 => array(2, 8), 18 => array(2, 8), 22 => array(2, 8), 24 => array(2, 8)), 5 => array(14 => array(2, 9), 18 => array(2, 9), 22 => array(2, 9), 24 => array(2, 9)), 6 => array(14 => array(2, 10), 18 => array(2, 10), 22 => array(2, 10), 24 => array(2, 10)), 7 => array(14 => array(2, 11), 18 => array(2, 11), 22 => array(2, 11), 24 => array(2, 11)), 8 => array(14 => array(2, 12), 18 => array(2, 12), 22 => array(2, 12), 24 => array(2, 12)), 9 => array(14 => array(2, 3), 18 => array(2, 3), 22 => array(2, 3), 24 => array(2, 3)), 10 => array(14 => array(2, 4), 18 => array(2, 4), 22 => array(2, 4), 24 => array(2, 4)), 11 => array(14 => array(2, 5), 18 => array(2, 5), 22 => array(2, 5), 24 => array(2, 5)), 12 => array(14 => array(2, 1), 18 => array(2, 1), 21 => array(2, 1), 22 => array(2, 1), 24 => array(2, 1)), 13 => array(14 => array(2, 2), 18 => array(2, 2), 22 => array(2, 2), 24 => array(2, 2)), 14 => array(3 => 20, 4 => array(1, 12), 18 => array(1, 17), 19 => 18, 20 => 19), 15 => array(3 => 5, 4 => array(1, 12), 5 => 6, 6 => array(1, 13), 7 => 3, 8 => array(1, 9), 9 => 4, 10 => array(1, 10), 11 => array(1, 11), 13 => 23, 15 => 7, 16 => 8, 17 => array(1, 14), 23 => array(1, 15), 24 => array(1, 21), 25 => 22), 16 => array(1 => array(2, 6)), 17 => array(14 => array(2, 13), 18 => array(2, 13), 22 => array(2, 13), 24 => array(2, 13)), 18 => array(18 => array(1, 24), 22 => array(1, 25)), 19 => array(18 => array(2, 16), 22 => array(2, 16)), 20 => array(21 => array(1, 26)), 21 => array(14 => array(2, 18), 18 => array(2, 18), 22 => array(2, 18), 24 => array(2, 18)), 22 => array(22 => array(1, 28), 24 => array(1, 27)), 23 => array(22 => array(2, 20), 24 => array(2, 20)), 24 => array(14 => array(2, 14), 18 => array(2, 14), 22 => array(2, 14), 24 => array(2, 14)), 25 => array(3 => 20, 4 => array(1, 12), 20 => 29), 26 => array(3 => 5, 4 => array(1, 12), 5 => 6, 6 => array(1, 13), 7 => 3, 8 => array(1, 9), 9 => 4, 10 => array(1, 10), 11 => array(1, 11), 13 => 30, 15 => 7, 16 => 8, 17 => array(1, 14), 23 => array(1, 15)), 27 => array(14 => array(2, 19), 18 => array(2, 19), 22 => array(2, 19), 24 => array(2, 19)), 28 => array(3 => 5, 4 => array(1, 12), 5 => 6, 6 => array(1, 13), 7 => 3, 8 => array(1, 9), 9 => 4, 10 => array(1, 10), 11 => array(1, 11), 13 => 31, 15 => 7, 16 => 8, 17 => array(1, 14), 23 => array(1, 15)), 29 => array(18 => array(2, 17), 22 => array(2, 17)), 30 => array(18 => array(2, 15), 22 => array(2, 15)), 31 => array(22 => array(2, 21), 24 => array(2, 21)));
    /**
    @const
    */
    private $defaultActions = array(16 => array(2, 6));
    public function lint($input, $flags = 0)
    {
        try {
            $this->parse($input, $flags);
        } catch (ParsingException $e) {
            return $e;
        }
        return null;
    }
    public function parse($input, $flags = 0)
    {
        $this->failOnBOM($input);
        $this->flags = $flags;
        $this->stack = array(0);
        $this->vstack = array(null);
        $this->lstack = array();
        $yytext = '';
        $yylineno = 0;
        $yyleng = 0;
        $recovering = 0;
        $this->lexer = new Lexer();
        $this->lexer->setInput($input);
        $yyloc = $this->lexer->yylloc;
        $this->lstack[] = $yyloc;
        $symbol = null;
        $preErrorSymbol = null;
        $action = null;
        $a = null;
        $r = null;
        $p = null;
        $len = null;
        $newState = null;
        $expected = null;
        $errStr = null;
        while (\true) {
            $state = $this->stack[\count($this->stack) - 1];
            if (isset($this->defaultActions[$state])) {
                $action = $this->defaultActions[$state];
            } else {
                if ($symbol === null) {
                    $symbol = $this->lexer->lex();
                }
                $action = isset($this->table[$state][$symbol]) ? $this->table[$state][$symbol] : \false;
            }
            if (!$action || !$action[0]) {
                \assert(isset($symbol));
                if (!$recovering) {
                    $expected = array();
                    foreach ($this->table[$state] as $p => $ignore) {
                        if (isset($this->terminals_[$p]) && $p > 2) {
                            $expected[] = "'" . $this->terminals_[$p] . "'";
                        }
                    }
                    $message = null;
                    if (\in_array("'STRING'", $expected) && \in_array(\substr($this->lexer->match, 0, 1), array('"', "'"))) {
                        $message = "Invalid string";
                        if ("'" === \substr($this->lexer->match, 0, 1)) {
                            $message .= ", it appears you used single quotes instead of double quotes";
                        } elseif (\preg_match('{".+?(\\\\[^"bfnrt/\\\\u](...)?)}', $this->lexer->getFullUpcomingInput(), $match)) {
                            $message .= ", it appears you have an unescaped backslash at: " . $match[1];
                        } elseif (\preg_match('{"(?:[^"]+|\\\\")*$}m', $this->lexer->getFullUpcomingInput())) {
                            $message .= ", it appears you forgot to terminate a string, or attempted to write a multiline string which is invalid";
                        }
                    }
                    $errStr = 'Parse error on line ' . ($yylineno + 1) . ":\n";
                    $errStr .= $this->lexer->showPosition() . "\n";
                    if ($message) {
                        $errStr .= $message;
                    } else {
                        $errStr .= \count($expected) > 1 ? "Expected one of: " : "Expected: ";
                        $errStr .= \implode(', ', $expected);
                    }
                    if (',' === \substr(\trim($this->lexer->getPastInput()), -1)) {
                        $errStr .= " - It appears you have an extra trailing comma";
                    }
                    $this->parseError($errStr, array('text' => $this->lexer->match, 'token' => isset($this->terminals_[$symbol]) ? $this->terminals_[$symbol] : $symbol, 'line' => $this->lexer->yylineno, 'loc' => $yyloc, 'expected' => $expected));
                }
                if ($recovering == 3) {
                    if ($symbol === Lexer::EOF) {
                        throw new ParsingException($errStr ?: 'Parsing halted.');
                    }
                    $yyleng = $this->lexer->yyleng;
                    $yytext = $this->lexer->yytext;
                    $yylineno = $this->lexer->yylineno;
                    $yyloc = $this->lexer->yylloc;
                    $symbol = $this->lexer->lex();
                }
                while (\true) {
                    if (\array_key_exists(Lexer::T_ERROR, $this->table[$state])) {
                        break;
                    }
                    if ($state == 0) {
                        throw new ParsingException($errStr ?: 'Parsing halted.');
                    }
                    $this->popStack(1);
                    $state = $this->stack[\count($this->stack) - 1];
                }
                $preErrorSymbol = $symbol;
                $symbol = Lexer::T_ERROR;
                $state = $this->stack[\count($this->stack) - 1];
                $action = isset($this->table[$state][Lexer::T_ERROR]) ? $this->table[$state][Lexer::T_ERROR] : \false;
                if ($action === \false) {
                    throw new \LogicException('No table value found for ' . $state . ' => ' . Lexer::T_ERROR);
                }
                $recovering = 3;
            }
            if (\is_array($action[0]) && \count($action) > 1) {
                throw new ParsingException('Parse Error: multiple actions possible at state: ' . $state . ', token: ' . $symbol);
            }
            switch ($action[0]) {
                case 1:
                    \assert(isset($symbol));
                    $this->stack[] = $symbol;
                    $this->vstack[] = $this->lexer->yytext;
                    $this->lstack[] = $this->lexer->yylloc;
                    $this->stack[] = $action[1];
                    $symbol = null;
                    if (!$preErrorSymbol) {
                        $yyleng = $this->lexer->yyleng;
                        $yytext = $this->lexer->yytext;
                        $yylineno = $this->lexer->yylineno;
                        $yyloc = $this->lexer->yylloc;
                        if ($recovering > 0) {
                            $recovering--;
                        }
                    } else {
                        $symbol = $preErrorSymbol;
                        $preErrorSymbol = null;
                    }
                    break;
                case 2:
                    $len = $this->productions_[$action[1]][1];
                    $currentToken = $this->vstack[\count($this->vstack) - $len];
                    $position = array('first_line' => $this->lstack[\count($this->lstack) - ($len ?: 1)]['first_line'], 'last_line' => $this->lstack[\count($this->lstack) - 1]['last_line'], 'first_column' => $this->lstack[\count($this->lstack) - ($len ?: 1)]['first_column'], 'last_column' => $this->lstack[\count($this->lstack) - 1]['last_column']);
                    list($newToken, $actionResult) = $this->performAction($currentToken, $yytext, $yyleng, $yylineno, $action[1]);
                    if (!$actionResult instanceof Undefined) {
                        return $actionResult;
                    }
                    if ($len) {
                        $this->popStack($len);
                    }
                    $this->stack[] = $this->productions_[$action[1]][0];
                    $this->vstack[] = $newToken;
                    $this->lstack[] = $position;
                    $newState = $this->table[$this->stack[\count($this->stack) - 2]][$this->stack[\count($this->stack) - 1]];
                    $this->stack[] = $newState;
                    break;
                case 3:
                    return \true;
            }
        }
    }
    protected function parseError($str, $hash = null)
    {
        throw new ParsingException($str, $hash ?: array());
    }
    private function performAction($currentToken, $yytext, $yyleng, $yylineno, $yystate)
    {
        $token = $currentToken;
        $len = \count($this->vstack) - 1;
        switch ($yystate) {
            case 1:
                $yytext = \preg_replace_callback('{(?:\\\\["bfnrt/\\\\]|\\\\u[a-fA-F0-9]{4})}', array($this, 'stringInterpolation'), $yytext);
                $token = $yytext;
                break;
            case 2:
                if (\strpos($yytext, 'e') !== \false || \strpos($yytext, 'E') !== \false) {
                    $token = \floatval($yytext);
                } else {
                    $token = \strpos($yytext, '.') === \false ? \intval($yytext) : \floatval($yytext);
                }
                break;
            case 3:
                $token = null;
                break;
            case 4:
                $token = \true;
                break;
            case 5:
                $token = \false;
                break;
            case 6:
                $token = $this->vstack[$len - 1];
                return array($token, $token);
            case 13:
                if ($this->flags & self::PARSE_TO_ASSOC) {
                    $token = array();
                } else {
                    $token = new stdClass();
                }
                break;
            case 14:
                $token = $this->vstack[$len - 1];
                break;
            case 15:
                $token = array($this->vstack[$len - 2], $this->vstack[$len]);
                break;
            case 16:
                \assert(\is_array($this->vstack[$len]));
                if (\PHP_VERSION_ID < 70100) {
                    $property = $this->vstack[$len][0] === '' ? '_empty_' : $this->vstack[$len][0];
                } else {
                    $property = $this->vstack[$len][0];
                }
                if ($this->flags & self::PARSE_TO_ASSOC) {
                    $token = array();
                    $token[$property] = $this->vstack[$len][1];
                } else {
                    $token = new stdClass();
                    $token->{$property} = $this->vstack[$len][1];
                }
                break;
            case 17:
                \assert(\is_array($this->vstack[$len]));
                if ($this->flags & self::PARSE_TO_ASSOC) {
                    \assert(\is_array($this->vstack[$len - 2]));
                    $token =& $this->vstack[$len - 2];
                    $key = $this->vstack[$len][0];
                    if ($this->flags & self::DETECT_KEY_CONFLICTS && isset($this->vstack[$len - 2][$key])) {
                        $errStr = 'Parse error on line ' . ($yylineno + 1) . ":\n";
                        $errStr .= $this->lexer->showPosition() . "\n";
                        $errStr .= "Duplicate key: " . $this->vstack[$len][0];
                        throw new DuplicateKeyException($errStr, $this->vstack[$len][0], array('line' => $yylineno + 1));
                    } elseif ($this->flags & self::ALLOW_DUPLICATE_KEYS && isset($this->vstack[$len - 2][$key])) {
                        $duplicateCount = 1;
                        do {
                            $duplicateKey = $key . '.' . $duplicateCount++;
                        } while (isset($this->vstack[$len - 2][$duplicateKey]));
                        $key = $duplicateKey;
                    }
                    $this->vstack[$len - 2][$key] = $this->vstack[$len][1];
                } else {
                    \assert($this->vstack[$len - 2] instanceof stdClass);
                    $token = $this->vstack[$len - 2];
                    if (\PHP_VERSION_ID < 70100) {
                        $key = $this->vstack[$len][0] === '' ? '_empty_' : $this->vstack[$len][0];
                    } else {
                        $key = $this->vstack[$len][0];
                    }
                    if ($this->flags & self::DETECT_KEY_CONFLICTS && isset($this->vstack[$len - 2]->{$key})) {
                        $errStr = 'Parse error on line ' . ($yylineno + 1) . ":\n";
                        $errStr .= $this->lexer->showPosition() . "\n";
                        $errStr .= "Duplicate key: " . $this->vstack[$len][0];
                        throw new DuplicateKeyException($errStr, $this->vstack[$len][0], array('line' => $yylineno + 1));
                    } elseif ($this->flags & self::ALLOW_DUPLICATE_KEYS && isset($this->vstack[$len - 2]->{$key})) {
                        $duplicateCount = 1;
                        do {
                            $duplicateKey = $key . '.' . $duplicateCount++;
                        } while (isset($this->vstack[$len - 2]->{$duplicateKey}));
                        $key = $duplicateKey;
                    }
                    $this->vstack[$len - 2]->{$key} = $this->vstack[$len][1];
                }
                break;
            case 18:
                $token = array();
                break;
            case 19:
                $token = $this->vstack[$len - 1];
                break;
            case 20:
                $token = array($this->vstack[$len]);
                break;
            case 21:
                \assert(\is_array($this->vstack[$len - 2]));
                $this->vstack[$len - 2][] = $this->vstack[$len];
                $token = $this->vstack[$len - 2];
                break;
        }
        return array($token, new Undefined());
    }
    private function stringInterpolation($match)
    {
        switch ($match[0]) {
            case '\\\\':
                return '\\';
            case '\\"':
                return '"';
            case '\\b':
                return \chr(8);
            case '\\f':
                return \chr(12);
            case '\\n':
                return "\n";
            case '\\r':
                return "\r";
            case '\\t':
                return "\t";
            case '\\/':
                return "/";
            default:
                return \html_entity_decode('&#x' . \ltrim(\substr($match[0], 2), '0') . ';', \ENT_QUOTES, 'UTF-8');
        }
    }
    private function popStack($n)
    {
        $this->stack = \array_slice($this->stack, 0, -(2 * $n));
        $this->vstack = \array_slice($this->vstack, 0, -$n);
        $this->lstack = \array_slice($this->lstack, 0, -$n);
    }
    private function failOnBOM($input)
    {
        $bom = "﻿";
        if (\substr($input, 0, 3) === $bom) {
            $this->parseError("BOM detected, make sure your input does not include a Unicode Byte-Order-Mark");
        }
    }
}
