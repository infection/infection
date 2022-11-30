<?php

namespace _HumbugBoxb47773b41c19\Amp\Process\Internal\Windows;

use _HumbugBoxb47773b41c19\Amp\ByteStream\ResourceInputStream;
use _HumbugBoxb47773b41c19\Amp\ByteStream\ResourceOutputStream;
use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Process\Internal\ProcessStatus;
use _HumbugBoxb47773b41c19\Amp\Process\ProcessException;
final class SocketConnector
{
    const SERVER_SOCKET_URI = 'tcp://127.0.0.1:0';
    const SECURITY_TOKEN_SIZE = 16;
    const CONNECT_TIMEOUT = 1000;
    private $server;
    private $pendingClients = [];
    private $pendingProcesses = [];
    public $address;
    public $port;
    public function __construct()
    {
        $flags = \STREAM_SERVER_LISTEN | \STREAM_SERVER_BIND;
        $this->server = \stream_socket_server(self::SERVER_SOCKET_URI, $errNo, $errStr, $flags);
        if (!$this->server) {
            throw new \Error("Failed to create TCP server socket for process wrapper: {$errNo}: {$errStr}");
        }
        if (!\stream_set_blocking($this->server, \false)) {
            throw new \Error("Failed to set server socket to non-blocking mode");
        }
        list($this->address, $this->port) = \explode(':', \stream_socket_get_name($this->server, \false));
        $this->port = (int) $this->port;
        Loop::unreference(Loop::onReadable($this->server, [$this, 'onServerSocketReadable']));
    }
    private function failClientHandshake($socket, int $code)
    {
        \fwrite($socket, \chr(SignalCode::HANDSHAKE_ACK) . \chr($code));
        \fclose($socket);
        unset($this->pendingClients[(int) $socket]);
    }
    public function failHandleStart(Handle $handle, string $message, ...$args)
    {
        Loop::cancel($handle->connectTimeoutWatcher);
        unset($this->pendingProcesses[$handle->wrapperPid]);
        foreach ($handle->sockets as $socket) {
            \fclose($socket);
        }
        $error = new ProcessException(\vsprintf($message, $args));
        $deferreds = $handle->stdioDeferreds;
        $deferreds[] = $handle->joinDeferred;
        $handle->stdioDeferreds = [];
        foreach ($deferreds as $deferred) {
            $deferred->fail($error);
        }
    }
    private function readDataFromPendingClient($socket, int $length, PendingSocketClient $state)
    {
        $data = \fread($socket, $length);
        if ($data === \false || $data === '') {
            return null;
        }
        $data = $state->receivedDataBuffer . $data;
        if (\strlen($data) < $length) {
            $state->receivedDataBuffer = $data;
            return null;
        }
        $state->receivedDataBuffer = '';
        Loop::cancel($state->readWatcher);
        return $data;
    }
    public function onReadableHandshake($watcher, $socket)
    {
        $socketId = (int) $socket;
        $pendingClient = $this->pendingClients[$socketId];
        if (null === ($data = $this->readDataFromPendingClient($socket, self::SECURITY_TOKEN_SIZE + 6, $pendingClient))) {
            return;
        }
        $packet = \unpack('Csignal/Npid/Cstream_id/a*client_token', $data);
        if ($packet['signal'] !== SignalCode::HANDSHAKE) {
            $this->failClientHandshake($socket, HandshakeStatus::SIGNAL_UNEXPECTED);
            return;
        }
        if ($packet['stream_id'] > 2) {
            $this->failClientHandshake($socket, HandshakeStatus::INVALID_STREAM_ID);
            return;
        }
        if (!isset($this->pendingProcesses[$packet['pid']])) {
            $this->failClientHandshake($socket, HandshakeStatus::INVALID_PROCESS_ID);
            return;
        }
        $handle = $this->pendingProcesses[$packet['pid']];
        if (isset($handle->sockets[$packet['stream_id']])) {
            $this->failClientHandshake($socket, HandshakeStatus::DUPLICATE_STREAM_ID);
            \trigger_error(\sprintf("%s: Received duplicate socket for process #%s stream #%d", self::class, $handle->pid, $packet['stream_id']), \E_USER_WARNING);
            return;
        }
        if (!\hash_equals($packet['client_token'], $handle->securityTokens[$packet['stream_id']])) {
            $this->failClientHandshake($socket, HandshakeStatus::INVALID_CLIENT_TOKEN);
            $this->failHandleStart($handle, "Invalid client security token for stream #%d", $packet['stream_id']);
            return;
        }
        $ackData = \chr(SignalCode::HANDSHAKE_ACK) . \chr(HandshakeStatus::SUCCESS) . $handle->securityTokens[$packet['stream_id'] + 3];
        if (\fwrite($socket, $ackData) !== self::SECURITY_TOKEN_SIZE + 2) {
            unset($this->pendingClients[$socketId]);
            return;
        }
        $pendingClient->pid = $packet['pid'];
        $pendingClient->streamId = $packet['stream_id'];
        $pendingClient->readWatcher = Loop::onReadable($socket, [$this, 'onReadableHandshakeAck']);
    }
    public function onReadableHandshakeAck($watcher, $socket)
    {
        $socketId = (int) $socket;
        $pendingClient = $this->pendingClients[$socketId];
        if (!isset($this->pendingProcesses[$pendingClient->pid]) || $this->pendingProcesses[$pendingClient->pid]->status === ProcessStatus::ENDED) {
            \fclose($socket);
            Loop::cancel($watcher);
            Loop::cancel($pendingClient->timeoutWatcher);
            unset($this->pendingClients[$socketId]);
            return;
        }
        if (null === ($data = $this->readDataFromPendingClient($socket, 2, $pendingClient))) {
            return;
        }
        Loop::cancel($pendingClient->timeoutWatcher);
        unset($this->pendingClients[$socketId]);
        $handle = $this->pendingProcesses[$pendingClient->pid];
        $packet = \unpack('Csignal/Cstatus', $data);
        if ($packet['signal'] !== SignalCode::HANDSHAKE_ACK || $packet['status'] !== HandshakeStatus::SUCCESS) {
            $this->failHandleStart($handle, "Client rejected handshake with code %d for stream #%d", $packet['status'], $pendingClient->streamId);
            return;
        }
        $handle->sockets[$pendingClient->streamId] = $socket;
        if (\count($handle->sockets) === 3) {
            $handle->childPidWatcher = Loop::onReadable($handle->sockets[0], [$this, 'onReadableChildPid'], $handle);
            $deferreds = $handle->stdioDeferreds;
            $handle->stdioDeferreds = [];
            $deferreds[0]->resolve(new ResourceOutputStream($handle->sockets[0]));
            $deferreds[1]->resolve(new ResourceInputStream($handle->sockets[1]));
            $deferreds[2]->resolve(new ResourceInputStream($handle->sockets[2]));
        }
    }
    public function onReadableChildPid($watcher, $socket, Handle $handle)
    {
        $data = \fread($socket, 5);
        if ($data === \false || $data === '') {
            return;
        }
        Loop::cancel($handle->childPidWatcher);
        Loop::cancel($handle->connectTimeoutWatcher);
        $handle->childPidWatcher = null;
        if (\strlen($data) !== 5) {
            $this->failHandleStart($handle, 'Failed to read PID from wrapper: Received %d of 5 expected bytes', \strlen($data));
            return;
        }
        $packet = \unpack('Csignal/Npid', $data);
        if ($packet['signal'] !== SignalCode::CHILD_PID) {
            $this->failHandleStart($handle, "Failed to read PID from wrapper: Unexpected signal code %d", $packet['signal']);
            return;
        }
        if ($handle->status === ProcessStatus::STARTING) {
            $handle->status = ProcessStatus::RUNNING;
            $handle->exitCodeWatcher = Loop::onReadable($handle->sockets[0], [$this, 'onReadableExitCode'], $handle);
            if (!$handle->exitCodeRequested) {
                Loop::unreference($handle->exitCodeWatcher);
            }
        }
        $handle->pidDeferred->resolve($packet['pid']);
        unset($this->pendingProcesses[$handle->wrapperPid]);
    }
    public function onReadableExitCode($watcher, $socket, Handle $handle)
    {
        $data = \fread($socket, 5);
        if ($data === \false || $data === '') {
            return;
        }
        Loop::cancel($handle->exitCodeWatcher);
        $handle->exitCodeWatcher = null;
        if (\strlen($data) !== 5) {
            $handle->status = ProcessStatus::ENDED;
            $handle->joinDeferred->fail(new ProcessException(\sprintf('Failed to read exit code from wrapper: Received %d of 5 expected bytes', \strlen($data))));
            return;
        }
        $packet = \unpack('Csignal/Ncode', $data);
        if ($packet['signal'] !== SignalCode::EXIT_CODE) {
            $this->failHandleStart($handle, "Failed to read exit code from wrapper: Unexpected signal code %d", $packet['signal']);
            return;
        }
        $handle->status = ProcessStatus::ENDED;
        $handle->joinDeferred->resolve($packet['code']);
        $handle->stdin->close();
        $handle->stdout->close();
        $handle->stderr->close();
        foreach ($handle->sockets as $sock) {
            if (\is_resource($sock)) {
                @\fclose($sock);
            }
        }
    }
    public function onClientSocketConnectTimeout($watcher, $socket)
    {
        $id = (int) $socket;
        Loop::cancel($this->pendingClients[$id]->readWatcher);
        unset($this->pendingClients[$id]);
        \fclose($socket);
    }
    public function onServerSocketReadable()
    {
        $socket = \stream_socket_accept($this->server);
        if (!\stream_set_blocking($socket, \false)) {
            throw new \Error("Failed to set client socket to non-blocking mode");
        }
        $pendingClient = new PendingSocketClient();
        $pendingClient->readWatcher = Loop::onReadable($socket, [$this, 'onReadableHandshake']);
        $pendingClient->timeoutWatcher = Loop::delay(self::CONNECT_TIMEOUT, [$this, 'onClientSocketConnectTimeout'], $socket);
        $this->pendingClients[(int) $socket] = $pendingClient;
    }
    public function onProcessConnectTimeout($watcher, Handle $handle)
    {
        $running = \is_resource($handle->proc) && \proc_get_status($handle->proc)['running'];
        $error = null;
        if (!$running) {
            $error = \stream_get_contents($handle->wrapperStderrPipe);
        }
        $error = $error ?: 'Process did not connect to server before timeout elapsed';
        foreach ($handle->sockets as $socket) {
            \fclose($socket);
        }
        $error = new ProcessException(\trim($error));
        foreach ($handle->stdioDeferreds as $deferred) {
            $deferred->fail($error);
        }
        \fclose($handle->wrapperStderrPipe);
        \proc_close($handle->proc);
        $handle->joinDeferred->fail($error);
    }
    public function registerPendingProcess(Handle $handle)
    {
        $handle->connectTimeoutWatcher = Loop::defer(function () use($handle) {
            $handle->connectTimeoutWatcher = Loop::delay(self::CONNECT_TIMEOUT, [$this, 'onProcessConnectTimeout'], $handle);
        });
        $this->pendingProcesses[$handle->wrapperPid] = $handle;
    }
}
