<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Descriptor\DescriptorInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Descriptor\JsonDescriptor;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Descriptor\MarkdownDescriptor;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Descriptor\TextDescriptor;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Descriptor\XmlDescriptor;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
class DescriptorHelper extends Helper
{
    private array $descriptors = [];
    public function __construct()
    {
        $this->register('txt', new TextDescriptor())->register('xml', new XmlDescriptor())->register('json', new JsonDescriptor())->register('md', new MarkdownDescriptor());
    }
    public function describe(OutputInterface $output, ?object $object, array $options = [])
    {
        $options = \array_merge(['raw_text' => \false, 'format' => 'txt'], $options);
        if (!isset($this->descriptors[$options['format']])) {
            throw new InvalidArgumentException(\sprintf('Unsupported format "%s".', $options['format']));
        }
        $descriptor = $this->descriptors[$options['format']];
        $descriptor->describe($output, $object, $options);
    }
    public function register(string $format, DescriptorInterface $descriptor) : static
    {
        $this->descriptors[$format] = $descriptor;
        return $this;
    }
    public function getName() : string
    {
        return 'descriptor';
    }
    public function getFormats() : array
    {
        return \array_keys($this->descriptors);
    }
}
