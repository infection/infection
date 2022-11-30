<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser;

class Comment implements \JsonSerializable
{
    protected $text;
    protected $startLine;
    protected $startFilePos;
    protected $startTokenPos;
    protected $endLine;
    protected $endFilePos;
    protected $endTokenPos;
    public function __construct(string $text, int $startLine = -1, int $startFilePos = -1, int $startTokenPos = -1, int $endLine = -1, int $endFilePos = -1, int $endTokenPos = -1)
    {
        $this->text = $text;
        $this->startLine = $startLine;
        $this->startFilePos = $startFilePos;
        $this->startTokenPos = $startTokenPos;
        $this->endLine = $endLine;
        $this->endFilePos = $endFilePos;
        $this->endTokenPos = $endTokenPos;
    }
    public function getText() : string
    {
        return $this->text;
    }
    public function getStartLine() : int
    {
        return $this->startLine;
    }
    public function getStartFilePos() : int
    {
        return $this->startFilePos;
    }
    public function getStartTokenPos() : int
    {
        return $this->startTokenPos;
    }
    public function getEndLine() : int
    {
        return $this->endLine;
    }
    public function getEndFilePos() : int
    {
        return $this->endFilePos;
    }
    public function getEndTokenPos() : int
    {
        return $this->endTokenPos;
    }
    public function getLine() : int
    {
        return $this->startLine;
    }
    public function getFilePos() : int
    {
        return $this->startFilePos;
    }
    public function getTokenPos() : int
    {
        return $this->startTokenPos;
    }
    public function __toString() : string
    {
        return $this->text;
    }
    public function getReformattedText()
    {
        $text = \trim($this->text);
        $newlinePos = \strpos($text, "\n");
        if (\false === $newlinePos) {
            return $text;
        } elseif (\preg_match('((*BSR_ANYCRLF)(*ANYCRLF)^.*(?:\\R\\s+\\*.*)+$)', $text)) {
            return \preg_replace('(^\\s+\\*)m', ' *', $this->text);
        } elseif (\preg_match('(^/\\*\\*?\\s*[\\r\\n])', $text) && \preg_match('(\\n(\\s*)\\*/$)', $text, $matches)) {
            return \preg_replace('(^' . \preg_quote($matches[1]) . ')m', '', $text);
        } elseif (\preg_match('(^/\\*\\*?\\s*(?!\\s))', $text, $matches)) {
            $prefixLen = $this->getShortestWhitespacePrefixLen(\substr($text, $newlinePos + 1));
            $removeLen = $prefixLen - \strlen($matches[0]);
            return \preg_replace('(^\\s{' . $removeLen . '})m', '', $text);
        }
        return $text;
    }
    private function getShortestWhitespacePrefixLen(string $str) : int
    {
        $lines = \explode("\n", $str);
        $shortestPrefixLen = \INF;
        foreach ($lines as $line) {
            \preg_match('(^\\s*)', $line, $matches);
            $prefixLen = \strlen($matches[0]);
            if ($prefixLen < $shortestPrefixLen) {
                $shortestPrefixLen = $prefixLen;
            }
        }
        return $shortestPrefixLen;
    }
    /**
    @psalm-return
    */
    public function jsonSerialize() : array
    {
        $type = $this instanceof Comment\Doc ? 'Comment_Doc' : 'Comment';
        return ['nodeType' => $type, 'text' => $this->text, 'line' => $this->startLine, 'filePos' => $this->startFilePos, 'tokenPos' => $this->startTokenPos, 'endLine' => $this->endLine, 'endFilePos' => $this->endFilePos, 'endTokenPos' => $this->endTokenPos];
    }
}
