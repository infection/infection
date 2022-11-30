<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Composer\XdebugHandler;

/**
@phpstan-type
*/
class PhpConfig
{
    public function useOriginal() : array
    {
        $this->getDataAndReset();
        return [];
    }
    public function useStandard() : array
    {
        $data = $this->getDataAndReset();
        if ($data !== null) {
            return ['-n', '-c', $data['tmpIni']];
        }
        return [];
    }
    public function usePersistent() : array
    {
        $data = $this->getDataAndReset();
        if ($data !== null) {
            $this->updateEnv('PHPRC', $data['tmpIni']);
            $this->updateEnv('PHP_INI_SCAN_DIR', '');
        }
        return [];
    }
    /**
    @phpstan-return
    */
    private function getDataAndReset() : ?array
    {
        $data = XdebugHandler::getRestartSettings();
        if ($data !== null) {
            $this->updateEnv('PHPRC', $data['phprc']);
            $this->updateEnv('PHP_INI_SCAN_DIR', $data['scanDir']);
        }
        return $data;
    }
    private function updateEnv(string $name, $value) : void
    {
        Process::setEnv($name, \false !== $value ? $value : null);
    }
}
