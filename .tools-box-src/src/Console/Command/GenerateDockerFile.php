<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console\Command;

use _HumbugBoxb47773b41c19\Fidry\Console\Command\CommandAware;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\CommandAwareness;
use _HumbugBoxb47773b41c19\Fidry\Console\Command\Configuration;
use _HumbugBoxb47773b41c19\Fidry\Console\ExitCode;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use function file_exists;
use function getcwd;
use function _HumbugBoxb47773b41c19\KevinGH\Box\create_temporary_phar;
use _HumbugBoxb47773b41c19\KevinGH\Box\DockerFileGenerator;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\dump_file;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\make_path_relative;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\remove;
use function realpath;
use function sprintf;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputArgument;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\StringInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Question\ConfirmationQuestion;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Path;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class GenerateDockerFile implements CommandAware
{
    use CommandAwareness;
    public const NAME = 'docker';
    private const PHAR_ARG = 'phar';
    private const DOCKER_FILE_NAME = 'Dockerfile';
    public function getConfiguration() : Configuration
    {
        return new Configuration('docker', '🐳  Generates a Dockerfile for the given PHAR', '', [new InputArgument(self::PHAR_ARG, InputArgument::OPTIONAL, 'The PHAR file')], [ConfigOption::getOptionInput()]);
    }
    public function execute(IO $io) : int
    {
        $pharFilePath = $this->getPharFilePath($io);
        if (null === $pharFilePath) {
            return ExitCode::FAILURE;
        }
        $io->newLine();
        $io->writeln(sprintf('🐳  Generating a Dockerfile for the PHAR "<comment>%s</comment>"', $pharFilePath));
        $tmpPharPath = create_temporary_phar($pharFilePath);
        $cleanUp = static fn() => remove($tmpPharPath);
        $requirementsFilePhar = 'phar://' . $tmpPharPath . '/.box/.requirements.php';
        try {
            return $this->generateFile($pharFilePath, $requirementsFilePhar, $io);
        } finally {
            $cleanUp();
        }
    }
    private function getPharFilePath(IO $io) : ?string
    {
        $pharFilePath = $io->getArgument(self::PHAR_ARG)->asNullableNonEmptyString();
        if (null === $pharFilePath) {
            $pharFilePath = $this->guessPharPath($io);
        }
        if (null === $pharFilePath) {
            return null;
        }
        $pharFilePath = Path::canonicalize($pharFilePath);
        Assert::file($pharFilePath);
        return \false !== realpath($pharFilePath) ? realpath($pharFilePath) : $pharFilePath;
    }
    private function guessPharPath(IO $io) : ?string
    {
        $config = ConfigOption::getConfig($io, \true);
        if (file_exists($config->getOutputPath())) {
            return $config->getOutputPath();
        }
        $compile = $io->askQuestion(new ConfirmationQuestion('The output PHAR could not be found, do you wish to generate it by running "<comment>box compile</comment>"?', \true));
        if (\false === $compile) {
            $io->error('Could not find the PHAR to generate the docker file for');
            return null;
        }
        $this->getCompileCommand()->execute(new IO(self::createCompileInput($io), clone $io->getOutput()));
        return $config->getOutputPath();
    }
    private function getCompileCommand() : Compile
    {
        return $this->getCommandRegistry()->findCommand(Compile::NAME);
    }
    private static function createCompileInput(IO $io) : InputInterface
    {
        if ($io->isQuiet()) {
            $compileInput = '--quiet';
        } elseif ($io->isVerbose()) {
            $compileInput = '--verbose 1';
        } elseif ($io->isVeryVerbose()) {
            $compileInput = '--verbose 2';
        } elseif ($io->isDebug()) {
            $compileInput = '--verbose 3';
        } else {
            $compileInput = '';
        }
        $compileInput = new StringInput($compileInput);
        $compileInput->setInteractive(\false);
        return $compileInput;
    }
    private function generateFile(string $pharPath, string $requirementsPhar, IO $io) : int
    {
        if (\false === file_exists($requirementsPhar)) {
            $io->error('Cannot retrieve the requirements for the PHAR. Make sure the PHAR has been built with Box and the requirement checker enabled.');
            return ExitCode::FAILURE;
        }
        $requirements = (include $requirementsPhar);
        $dockerFileContents = DockerFileGenerator::createForRequirements($requirements, make_path_relative($pharPath, getcwd()))->generateStub();
        if (file_exists(self::DOCKER_FILE_NAME)) {
            $remove = $io->askQuestion(new ConfirmationQuestion('A Docker file has already been found, are you sure you want to override it?', \true));
            if (\false === $remove) {
                $io->writeln('Skipped the docker file generation.');
                return ExitCode::SUCCESS;
            }
        }
        dump_file(self::DOCKER_FILE_NAME, $dockerFileContents);
        $io->success('Done');
        $io->writeln([sprintf('You can now inspect your <comment>%s</comment> file or build your container with:', self::DOCKER_FILE_NAME), '$ <comment>docker build .</comment>']);
        return ExitCode::SUCCESS;
    }
}
