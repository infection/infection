<?php

namespace _HumbugBox9658796bb9f0\ColinODell\Json5;

final class SyntaxError extends \JsonException
{
    private $lineNumber;
    private $column;
    public function __construct($message, $linenumber, $columnNumber, $previous = null)
    {
        $message = \sprintf('%s at line %d column %d of the JSON5 data', $message, $linenumber, $columnNumber);
        parent::__construct($message, 0, $previous);
        $this->lineNumber = $linenumber;
        $this->column = $columnNumber;
    }
    public function getLineNumber()
    {
        return $this->lineNumber;
    }
    public function getColumn()
    {
        return $this->column;
    }
}
