<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Dumper;

use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Data;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
class ContextualizedDumper implements DataDumperInterface
{
    private DataDumperInterface $wrappedDumper;
    private array $contextProviders;
    public function __construct(DataDumperInterface $wrappedDumper, array $contextProviders)
    {
        $this->wrappedDumper = $wrappedDumper;
        $this->contextProviders = $contextProviders;
    }
    public function dump(Data $data)
    {
        $context = [];
        foreach ($this->contextProviders as $contextProvider) {
            $context[\get_class($contextProvider)] = $contextProvider->getContext();
        }
        $this->wrappedDumper->dump($data->withContext($context));
    }
}
