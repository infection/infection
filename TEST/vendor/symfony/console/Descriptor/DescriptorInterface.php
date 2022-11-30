<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Descriptor;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
interface DescriptorInterface
{
    public function describe(OutputInterface $output, object $object, array $options = []);
}
