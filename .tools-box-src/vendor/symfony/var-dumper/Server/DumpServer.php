<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Server;

use _HumbugBoxb47773b41c19\Psr\Log\LoggerInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Data;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Stub;
class DumpServer
{
    private string $host;
    private ?LoggerInterface $logger;
    private $socket;
    public function __construct(string $host, LoggerInterface $logger = null)
    {
        if (!\str_contains($host, '://')) {
            $host = 'tcp://' . $host;
        }
        $this->host = $host;
        $this->logger = $logger;
    }
    public function start() : void
    {
        if (!($this->socket = \stream_socket_server($this->host, $errno, $errstr))) {
            throw new \RuntimeException(\sprintf('Server start failed on "%s": ', $this->host) . $errstr . ' ' . $errno);
        }
    }
    public function listen(callable $callback) : void
    {
        if (null === $this->socket) {
            $this->start();
        }
        foreach ($this->getMessages() as $clientId => $message) {
            $this->logger?->info('Received a payload from client {clientId}', ['clientId' => $clientId]);
            $payload = @\unserialize(\base64_decode($message), ['allowed_classes' => [Data::class, Stub::class]]);
            if (\false === $payload) {
                $this->logger?->warning('Unable to decode a message from {clientId} client.', ['clientId' => $clientId]);
                continue;
            }
            if (!\is_array($payload) || \count($payload) < 2 || !$payload[0] instanceof Data || !\is_array($payload[1])) {
                $this->logger?->warning('Invalid payload from {clientId} client. Expected an array of two elements (Data $data, array $context)', ['clientId' => $clientId]);
                continue;
            }
            [$data, $context] = $payload;
            $callback($data, $context, $clientId);
        }
    }
    public function getHost() : string
    {
        return $this->host;
    }
    private function getMessages() : iterable
    {
        $sockets = [(int) $this->socket => $this->socket];
        $write = [];
        while (\true) {
            $read = $sockets;
            \stream_select($read, $write, $write, null);
            foreach ($read as $stream) {
                if ($this->socket === $stream) {
                    $stream = \stream_socket_accept($this->socket);
                    $sockets[(int) $stream] = $stream;
                } elseif (\feof($stream)) {
                    unset($sockets[(int) $stream]);
                    \fclose($stream);
                } else {
                    (yield (int) $stream => \fgets($stream));
                }
            }
        }
    }
}
