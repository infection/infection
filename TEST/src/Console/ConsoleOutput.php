<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console;

use function implode;
use _HumbugBox9658796bb9f0\Infection\Logger\ConsoleLogger;
use const PHP_EOL;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
class ConsoleOutput
{
    private const RUNNING_WITH_DEBUGGER_NOTE = 'You are running Infection with %s enabled.';
    private const MIN_MSI_CAN_GET_INCREASED_NOTICE = 'The %s is %s%% percentage points over the required %s. Consider increasing the required %s percentage the next time you run Infection.';
    public function __construct(private ConsoleLogger $logger)
    {
    }
    public function logVerbosityDeprecationNotice(string $valueToUse) : void
    {
        $this->logger->notice('Numeric versions of log-verbosity have been deprecated, please use, ' . $valueToUse . ' to keep the same result', ['block' => \true]);
    }
    public function logUnknownVerbosityOption(string $default) : void
    {
        $this->logger->notice('Running infection with an unknown log-verbosity option, falling back to ' . $default . ' option', ['block' => \true]);
    }
    public function logMinMsiCanGetIncreasedNotice(float $minMsi, float $msi) : void
    {
        $typeString = 'MSI';
        $msiDifference = $msi - $minMsi;
        $this->logger->notice(sprintf(self::MIN_MSI_CAN_GET_INCREASED_NOTICE, $typeString, $msiDifference, $typeString, $typeString), ['block' => \true]);
    }
    public function logMinCoveredCodeMsiCanGetIncreasedNotice(float $minMsi, float $coveredCodeMsi) : void
    {
        $typeString = 'Covered Code MSI';
        $msiDifference = $coveredCodeMsi - $minMsi;
        $this->logger->notice(sprintf(self::MIN_MSI_CAN_GET_INCREASED_NOTICE, $typeString, $msiDifference, $typeString, $typeString), ['block' => \true]);
    }
    public function logRunningWithDebugger(string $debugger) : void
    {
        $this->logger->notice(sprintf(self::RUNNING_WITH_DEBUGGER_NOTE, $debugger));
    }
    public function logNotInControlOfExitCodes() : void
    {
        $this->logger->warning('Infection cannot control exit codes and unable to relaunch itself.' . PHP_EOL . 'It is your responsibility to disable xdebug/phpdbg unless needed.', ['block' => \true]);
    }
    public function logSkippingInitialTests() : void
    {
        $this->logger->warning(implode(PHP_EOL, ['Skipping the initial test run can be very dangerous.', 'It is your responsibility to ensure the tests are in a passing state to begin.', 'If this is not done then mutations may report as caught when they are not.']));
    }
}
