<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Descriptor;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
interface DescriptorInterface
{
    public function describe(OutputInterface $output, object $object, array $options = []);
}
