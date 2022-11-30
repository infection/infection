<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Parser;

use _HumbugBox9658796bb9f0\PhpParser\Error;
use _HumbugBox9658796bb9f0\PhpParser\ErrorHandler;
use _HumbugBox9658796bb9f0\PhpParser\Parser;
class Multiple implements Parser
{
    private $parsers;
    public function __construct(array $parsers)
    {
        $this->parsers = $parsers;
    }
    public function parse(string $code, ErrorHandler $errorHandler = null)
    {
        if (null === $errorHandler) {
            $errorHandler = new ErrorHandler\Throwing();
        }
        list($firstStmts, $firstError) = $this->tryParse($this->parsers[0], $errorHandler, $code);
        if ($firstError === null) {
            return $firstStmts;
        }
        for ($i = 1, $c = \count($this->parsers); $i < $c; ++$i) {
            list($stmts, $error) = $this->tryParse($this->parsers[$i], $errorHandler, $code);
            if ($error === null) {
                return $stmts;
            }
        }
        throw $firstError;
    }
    private function tryParse(Parser $parser, ErrorHandler $errorHandler, $code)
    {
        $stmts = null;
        $error = null;
        try {
            $stmts = $parser->parse($code, $errorHandler);
        } catch (Error $error) {
        }
        return [$stmts, $error];
    }
}
