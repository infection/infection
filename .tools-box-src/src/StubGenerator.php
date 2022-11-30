<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box;

use function addcslashes;
use function implode;
use function str_replace;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class StubGenerator
{
    private const CHECK_FILE_NAME = 'bin/check-requirements.php';
    private const STUB_TEMPLATE = <<<'STUB'
__BOX_SHEBANG__
<?php
__BOX_BANNER__

__BOX_PHAR_CONFIG__

__HALT_COMPILER(); ?>

STUB;
    private ?string $alias = null;
    private ?string $banner = null;
    private ?string $index = null;
    private bool $intercept = \false;
    private ?string $shebang = null;
    private bool $checkRequirements = \true;
    public static function create() : self
    {
        return new self();
    }
    public function generateStub() : string
    {
        $stub = self::STUB_TEMPLATE;
        $stub = str_replace("__BOX_SHEBANG__\n", null === $this->shebang ? '' : $this->shebang . "\n", $stub);
        $stub = str_replace("__BOX_BANNER__\n", $this->generateBannerStmt(), $stub);
        $stub = str_replace("__BOX_PHAR_CONFIG__\n", $this->generatePharConfigStmt(), $stub);
        return $stub;
    }
    public function alias(?string $alias) : self
    {
        $this->alias = $alias;
        return $this;
    }
    public function banner(?string $banner) : self
    {
        $this->banner = $banner;
        return $this;
    }
    public function index(?string $index) : self
    {
        $this->index = $index;
        return $this;
    }
    public function intercept(bool $intercept) : self
    {
        $this->intercept = $intercept;
        return $this;
    }
    public function shebang(?string $shebang) : self
    {
        if (null !== $shebang) {
            Assert::notEmpty($shebang, 'Cannot use an empty string for the shebang.');
        }
        $this->shebang = $shebang;
        return $this;
    }
    public function getShebang() : ?string
    {
        return $this->shebang;
    }
    public function checkRequirements(bool $checkRequirements) : self
    {
        $this->checkRequirements = $checkRequirements;
        return $this;
    }
    private function arg(string $arg, string $quote = "'") : string
    {
        return $quote . addcslashes($arg, $quote) . $quote;
    }
    private function getAliasStmt() : ?string
    {
        return null !== $this->alias ? 'Phar::mapPhar(' . $this->arg($this->alias) . ');' : null;
    }
    private function generateBannerStmt() : string
    {
        if (null === $this->banner) {
            return '';
        }
        $banner = "/*\n * ";
        $banner .= str_replace(" \n", "\n", str_replace("\n", "\n * ", $this->banner));
        $banner .= "\n */";
        return "\n" . $banner . "\n";
    }
    private function generatePharConfigStmt() : string
    {
        $previous = \false;
        $stub = [];
        if (null !== ($aliasStmt = $this->getAliasStmt())) {
            $stub[] = $aliasStmt;
            $previous = \true;
        }
        if ($this->intercept) {
            $stub[] = 'Phar::interceptFileFuncs();';
            $previous = \true;
        }
        if (\false !== $this->checkRequirements) {
            if ($previous) {
                $stub[] = '';
            }
            $checkRequirementsFile = self::CHECK_FILE_NAME;
            $stub[] = null === $this->alias ? "require 'phar://' . __FILE__ . '/.box/{$checkRequirementsFile}';" : "require 'phar://{$this->alias}/.box/{$checkRequirementsFile}';";
            $previous = \true;
        }
        if (null !== $this->index) {
            if ($previous) {
                $stub[] = '';
            }
            $stub[] = null === $this->alias ? "require 'phar://' . __FILE__ . '/{$this->index}';" : "require 'phar://{$this->alias}/{$this->index}';";
        }
        if ([] === $stub) {
            return "// No PHAR config\n";
        }
        return implode("\n", $stub) . "\n";
    }
}
