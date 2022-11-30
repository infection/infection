<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Yaml\Exception;

class ParseException extends RuntimeException
{
    private $parsedFile;
    private $parsedLine;
    private $snippet;
    private $rawMessage;
    public function __construct(string $message, int $parsedLine = -1, string $snippet = null, string $parsedFile = null, \Throwable $previous = null)
    {
        $this->parsedFile = $parsedFile;
        $this->parsedLine = $parsedLine;
        $this->snippet = $snippet;
        $this->rawMessage = $message;
        $this->updateRepr();
        parent::__construct($this->message, 0, $previous);
    }
    public function getSnippet()
    {
        return $this->snippet;
    }
    public function setSnippet(string $snippet)
    {
        $this->snippet = $snippet;
        $this->updateRepr();
    }
    public function getParsedFile()
    {
        return $this->parsedFile;
    }
    public function setParsedFile(string $parsedFile)
    {
        $this->parsedFile = $parsedFile;
        $this->updateRepr();
    }
    public function getParsedLine()
    {
        return $this->parsedLine;
    }
    public function setParsedLine(int $parsedLine)
    {
        $this->parsedLine = $parsedLine;
        $this->updateRepr();
    }
    private function updateRepr()
    {
        $this->message = $this->rawMessage;
        $dot = \false;
        if ('.' === \substr($this->message, -1)) {
            $this->message = \substr($this->message, 0, -1);
            $dot = \true;
        }
        if (null !== $this->parsedFile) {
            $this->message .= \sprintf(' in %s', \json_encode($this->parsedFile, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE));
        }
        if ($this->parsedLine >= 0) {
            $this->message .= \sprintf(' at line %d', $this->parsedLine);
        }
        if ($this->snippet) {
            $this->message .= \sprintf(' (near "%s")', $this->snippet);
        }
        if ($dot) {
            $this->message .= '.';
        }
    }
}
