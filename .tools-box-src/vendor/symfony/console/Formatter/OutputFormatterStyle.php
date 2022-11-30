<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Color;
class OutputFormatterStyle implements OutputFormatterStyleInterface
{
    private Color $color;
    private string $foreground;
    private string $background;
    private array $options;
    private ?string $href = null;
    private bool $handlesHrefGracefully;
    public function __construct(string $foreground = null, string $background = null, array $options = [])
    {
        $this->color = new Color($this->foreground = $foreground ?: '', $this->background = $background ?: '', $this->options = $options);
    }
    public function setForeground(string $color = null)
    {
        $this->color = new Color($this->foreground = $color ?: '', $this->background, $this->options);
    }
    public function setBackground(string $color = null)
    {
        $this->color = new Color($this->foreground, $this->background = $color ?: '', $this->options);
    }
    public function setHref(string $url) : void
    {
        $this->href = $url;
    }
    public function setOption(string $option)
    {
        $this->options[] = $option;
        $this->color = new Color($this->foreground, $this->background, $this->options);
    }
    public function unsetOption(string $option)
    {
        $pos = \array_search($option, $this->options);
        if (\false !== $pos) {
            unset($this->options[$pos]);
        }
        $this->color = new Color($this->foreground, $this->background, $this->options);
    }
    public function setOptions(array $options)
    {
        $this->color = new Color($this->foreground, $this->background, $this->options = $options);
    }
    public function apply(string $text) : string
    {
        $this->handlesHrefGracefully ??= 'JetBrains-JediTerm' !== \getenv('TERMINAL_EMULATOR') && (!\getenv('KONSOLE_VERSION') || (int) \getenv('KONSOLE_VERSION') > 201100);
        if (null !== $this->href && $this->handlesHrefGracefully) {
            $text = "\x1b]8;;{$this->href}\x1b\\{$text}\x1b]8;;\x1b\\";
        }
        return $this->color->apply($text);
    }
}
