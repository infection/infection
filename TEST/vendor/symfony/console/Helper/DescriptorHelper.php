<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Descriptor\DescriptorInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Descriptor\JsonDescriptor;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Descriptor\MarkdownDescriptor;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Descriptor\TextDescriptor;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Descriptor\XmlDescriptor;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
class DescriptorHelper extends Helper
{
    private $descriptors = [];
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
    public function register(string $format, DescriptorInterface $descriptor)
    {
        $this->descriptors[$format] = $descriptor;
        return $this;
    }
    public function getName()
    {
        return 'descriptor';
    }
    public function getFormats() : array
    {
        return \array_keys($this->descriptors);
    }
}
