<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console;

use _HumbugBoxb47773b41c19\Fidry\Console\Application\Application as FidryApplication;
use function _HumbugBoxb47773b41c19\KevinGH\Box\get_box_version;
use function sprintf;
use function trim;
final class Application implements FidryApplication
{
    private string $version;
    private string $releaseDate;
    private string $header;
    public function __construct(private string $name = 'Box', ?string $version = null, string $releaseDate = '2022-11-21 22:20:19 UTC', private bool $autoExit = \true, private bool $catchExceptions = \true)
    {
        $this->version = $version ?? get_box_version();
        $this->releaseDate = !\str_contains($releaseDate, '@') ? $releaseDate : '';
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getVersion() : string
    {
        return $this->version;
    }
    public function getLongVersion() : string
    {
        return trim(sprintf('<info>%s</info> version <comment>%s</comment> %s', $this->getName(), $this->getVersion(), $this->releaseDate));
    }
    public function getHelp() : string
    {
        return $this->getHeader();
    }
    public function getHeader() : string
    {
        if (!isset($this->header)) {
            $this->header = Logo::LOGO_ASCII . $this->getLongVersion();
        }
        return $this->header;
    }
    public function getCommands() : array
    {
        return [new Command\Compile($this->getHeader()), new Command\Diff(), new Command\Info(), new Command\Process(), new Command\Extract(), new Command\Validate(), new Command\Verify(), new Command\GenerateDockerFile(), new Command\Namespace_()];
    }
    public function getDefaultCommand() : string
    {
        return 'list';
    }
    public function isAutoExitEnabled() : bool
    {
        return $this->autoExit;
    }
    public function areExceptionsCaught() : bool
    {
        return $this->catchExceptions;
    }
}
