<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser;

class Error extends \RuntimeException
{
    protected $rawMessage;
    protected $attributes;
    public function __construct(string $message, $attributes = [])
    {
        $this->rawMessage = $message;
        if (\is_array($attributes)) {
            $this->attributes = $attributes;
        } else {
            $this->attributes = ['startLine' => $attributes];
        }
        $this->updateMessage();
    }
    public function getRawMessage() : string
    {
        return $this->rawMessage;
    }
    public function getStartLine() : int
    {
        return $this->attributes['startLine'] ?? -1;
    }
    public function getEndLine() : int
    {
        return $this->attributes['endLine'] ?? -1;
    }
    public function getAttributes() : array
    {
        return $this->attributes;
    }
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        $this->updateMessage();
    }
    public function setRawMessage(string $message)
    {
        $this->rawMessage = $message;
        $this->updateMessage();
    }
    public function setStartLine(int $line)
    {
        $this->attributes['startLine'] = $line;
        $this->updateMessage();
    }
    public function hasColumnInfo() : bool
    {
        return isset($this->attributes['startFilePos'], $this->attributes['endFilePos']);
    }
    public function getStartColumn(string $code) : int
    {
        if (!$this->hasColumnInfo()) {
            throw new \RuntimeException('Error does not have column information');
        }
        return $this->toColumn($code, $this->attributes['startFilePos']);
    }
    public function getEndColumn(string $code) : int
    {
        if (!$this->hasColumnInfo()) {
            throw new \RuntimeException('Error does not have column information');
        }
        return $this->toColumn($code, $this->attributes['endFilePos']);
    }
    public function getMessageWithColumnInfo(string $code) : string
    {
        return \sprintf('%s from %d:%d to %d:%d', $this->getRawMessage(), $this->getStartLine(), $this->getStartColumn($code), $this->getEndLine(), $this->getEndColumn($code));
    }
    private function toColumn(string $code, int $pos) : int
    {
        if ($pos > \strlen($code)) {
            throw new \RuntimeException('Invalid position information');
        }
        $lineStartPos = \strrpos($code, "\n", $pos - \strlen($code));
        if (\false === $lineStartPos) {
            $lineStartPos = -1;
        }
        return $pos - $lineStartPos;
    }
    protected function updateMessage()
    {
        $this->message = $this->rawMessage;
        if (-1 === $this->getStartLine()) {
            $this->message .= ' on unknown line';
        } else {
            $this->message .= ' on line ' . $this->getStartLine();
        }
    }
}
