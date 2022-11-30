<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Context\Internal;

use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\Channel;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ChannelException;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ChannelledSocket;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ExitFailure;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\ExitSuccess;
use _HumbugBoxb47773b41c19\Amp\Parallel\Sync\SerializationException;
use function _HumbugBoxb47773b41c19\Amp\call;
final class Thread extends \Thread
{
    const KILL_CHECK_FREQUENCY = 250;
    private $id;
    private $function;
    private $args;
    private $socket;
    private $killed = \false;
    public function __construct(int $id, $socket, callable $function, array $args = [])
    {
        $this->id = $id;
        $this->function = $function;
        $this->args = $args;
        $this->socket = $socket;
    }
    public function run()
    {
        \define("AMP_CONTEXT", "thread");
        \define("AMP_CONTEXT_ID", $this->id);
        (static function () : void {
            $paths = [\dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . "vendor" . \DIRECTORY_SEPARATOR . "autoload.php", \dirname(__DIR__, 5) . \DIRECTORY_SEPARATOR . "autoload.php"];
            foreach ($paths as $path) {
                if (\file_exists($path)) {
                    $autoloadPath = $path;
                    break;
                }
            }
            if (!isset($autoloadPath)) {
                throw new \Error("Could not locate autoload.php");
            }
            require $autoloadPath;
        })->bindTo(null, null)();
        if ($this->killed) {
            return;
        }
        Loop::run(function () : \Generator {
            $watcher = Loop::repeat(self::KILL_CHECK_FREQUENCY, function () : void {
                if ($this->killed) {
                    Loop::stop();
                }
            });
            Loop::unreference($watcher);
            try {
                $channel = new ChannelledSocket($this->socket, $this->socket);
                yield from $this->execute($channel);
            } catch (\Throwable $exception) {
                return;
            } finally {
                Loop::cancel($watcher);
            }
        });
    }
    public function kill()
    {
        return $this->killed = \true;
    }
    private function execute(Channel $channel) : \Generator
    {
        try {
            $result = new ExitSuccess((yield call($this->function, $channel, ...$this->args)));
        } catch (\Throwable $exception) {
            $result = new ExitFailure($exception);
        }
        if ($this->killed) {
            return;
        }
        try {
            try {
                (yield $channel->send($result));
            } catch (SerializationException $exception) {
                (yield $channel->send(new ExitFailure($exception)));
            }
        } catch (ChannelException $exception) {
        }
    }
}
