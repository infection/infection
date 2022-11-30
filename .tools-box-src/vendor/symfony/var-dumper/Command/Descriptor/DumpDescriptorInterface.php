<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Command\Descriptor;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Data;
interface DumpDescriptorInterface
{
    public function describe(OutputInterface $output, Data $data, array $context, int $clientId) : void;
}
