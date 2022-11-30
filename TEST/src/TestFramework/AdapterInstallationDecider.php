<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework;

use function array_key_exists;
use function class_exists;
use _HumbugBox9658796bb9f0\Infection\Console\IO;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\QuestionHelper;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Question\ConfirmationQuestion;
final class AdapterInstallationDecider
{
    private const ADAPTER_NAME_TO_CLASS_MAP = [TestFrameworkTypes::CODECEPTION => '_HumbugBox9658796bb9f0\\Infection\\TestFramework\\Codeception\\CodeceptionAdapter', TestFrameworkTypes::PHPSPEC => '_HumbugBox9658796bb9f0\\Infection\\TestFramework\\PhpSpec\\PhpSpecAdapter'];
    public function __construct(private QuestionHelper $questionHelper)
    {
    }
    public function shouldBeInstalled(string $adapterName, IO $io) : bool
    {
        if (!array_key_exists($adapterName, self::ADAPTER_NAME_TO_CLASS_MAP) || class_exists(self::ADAPTER_NAME_TO_CLASS_MAP[$adapterName])) {
            return \false;
        }
        $io->newLine();
        $question = new ConfirmationQuestion(sprintf(<<<TEXT
We noticed you are using a test framework supported by an external Infection plugin.
Would you like to install <comment>%s</comment>? [<comment>yes</comment>]:
TEXT
, AdapterInstaller::OFFICIAL_ADAPTERS_MAP[$adapterName]), \true);
        return $this->questionHelper->ask($io->getInput(), $io->getOutput(), $question);
    }
}
