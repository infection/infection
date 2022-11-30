<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Output;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter\OutputFormatterInterface;
class StreamOutput extends Output
{
    private $stream;
    public function __construct($stream, int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = null, OutputFormatterInterface $formatter = null)
    {
        if (!\is_resource($stream) || 'stream' !== \get_resource_type($stream)) {
            throw new InvalidArgumentException('The StreamOutput class needs a stream as its first argument.');
        }
        $this->stream = $stream;
        if (null === $decorated) {
            $decorated = $this->hasColorSupport();
        }
        parent::__construct($verbosity, $decorated, $formatter);
    }
    public function getStream()
    {
        return $this->stream;
    }
    protected function doWrite(string $message, bool $newline)
    {
        if ($newline) {
            $message .= \PHP_EOL;
        }
        @\fwrite($this->stream, $message);
        \fflush($this->stream);
    }
    protected function hasColorSupport()
    {
        if (isset($_SERVER['NO_COLOR']) || \false !== \getenv('NO_COLOR')) {
            return \false;
        }
        if ('Hyper' === \getenv('TERM_PROGRAM')) {
            return \true;
        }
        if (\DIRECTORY_SEPARATOR === '\\') {
            return \function_exists('sapi_windows_vt100_support') && @\sapi_windows_vt100_support($this->stream) || \false !== \getenv('ANSICON') || 'ON' === \getenv('ConEmuANSI') || 'xterm' === \getenv('TERM');
        }
        return \stream_isatty($this->stream);
    }
}
