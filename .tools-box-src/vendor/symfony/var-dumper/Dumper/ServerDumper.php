<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Dumper;

use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Data;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Server\Connection;
class ServerDumper implements DataDumperInterface
{
    private Connection $connection;
    private ?DataDumperInterface $wrappedDumper;
    public function __construct(string $host, DataDumperInterface $wrappedDumper = null, array $contextProviders = [])
    {
        $this->connection = new Connection($host, $contextProviders);
        $this->wrappedDumper = $wrappedDumper;
    }
    public function getContextProviders() : array
    {
        return $this->connection->getContextProviders();
    }
    public function dump(Data $data)
    {
        if (!$this->connection->write($data) && $this->wrappedDumper) {
            $this->wrappedDumper->dump($data);
        }
    }
}
