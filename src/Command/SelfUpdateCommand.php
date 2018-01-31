<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Humbug\SelfUpdate\Updater;
use Humbug\SelfUpdate\VersionParser;
use Humbug\SelfUpdate\Strategy\GithubStrategy;

class SelfUpdateCommand extends Command
{
    const PACKAGE_NAME = 'infection/infection';
    const FILE_NAME = 'infection.phar';

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $version;

    protected function configure()
    {
        $this->setName('self-update')
            ->setDescription('Update infection.phar to most recent stable or pre-release build.')
            ->addOption(
                'pre',
                'p',
                InputOption::VALUE_NONE,
                'Update to most recent pre-release version of Infection (alpha/beta/rc) tagged on Github.'
            )
            ->addOption(
                'stable',
                's',
                InputOption::VALUE_NONE,
                'Update to most recent stable version tagged on Github.'
            )
            ->addOption(
                'rollback',
                'r',
                InputOption::VALUE_NONE,
                'Rollback to previous version of Infection if available on filesystem.'
            )
            ->addOption(
                'check',
                'c',
                InputOption::VALUE_NONE,
                'Checks what updates are available across all possible stability tracks.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->version = $this->getApplication()->getVersion();
        $parser = new VersionParser();

        if ($input->getOption('rollback')) {
            $this->rollback();

            return 0;
        }

        if ($input->getOption('check')) {
            $this->printAvailableUpdates();

            return 0;
        }

        if ($input->getOption('pre')) {
            $this->updateToPreReleaseBuild();

            return 0;
        }

        if ($input->getOption('stable')) {
            $this->updateToStableBuild();

            return 0;
        }

        /*
         * If current build is stable, only update to more recent stable
         * versions if available. User may specify otherwise using options.
         */
        if ($parser->isStable($this->version)) {
            $this->updateToStableBuild();

            return 0;
        }

        $output->writeln('Please choose what version do you want to update to.');

        return 1;
    }

    protected function getStableUpdater(): Updater
    {
        $updater = new Updater();
        $updater->setStrategy(Updater::STRATEGY_GITHUB);

        return $this->getGithubReleasesUpdater($updater);
    }

    protected function getPreReleaseUpdater(): Updater
    {
        $updater = new Updater();
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setStability(GithubStrategy::UNSTABLE);

        return $this->getGithubReleasesUpdater($updater);
    }

    protected function getGithubReleasesUpdater(Updater $updater): Updater
    {
        $updater->getStrategy()->setPackageName(self::PACKAGE_NAME);
        $updater->getStrategy()->setPharName(self::FILE_NAME);
        $updater->getStrategy()->setCurrentLocalVersion($this->version);

        return $updater;
    }

    protected function updateToStableBuild()
    {
        $this->update($this->getStableUpdater());
    }

    protected function updateToPreReleaseBuild()
    {
        $this->update($this->getPreReleaseUpdater());
    }

    protected function update(Updater $updater)
    {
        $this->output->writeln('Updating...' . PHP_EOL);
        try {
            $result = $updater->update();

            $oldVersion = $updater->getOldVersion();

            if ($result) {
                $newVersion = $updater->getNewVersion();

                $this->output->writeln('<fg=green>Infection has been updated.</fg=green>');
                $this->output->writeln(sprintf(
                    '<fg=green>Current version is:</fg=green> <options=bold>%s</options=bold>.',
                    $newVersion
                ));
                $this->output->writeln(sprintf(
                    '<fg=green>Previous version was:</fg=green> <options=bold>%s</options=bold>.',
                    $oldVersion
                ));
            } else {
                $this->output->writeln('<fg=green>Infection is currently up to date.</fg=green>');
                $this->output->writeln(sprintf(
                    '<fg=green>Current version is:</fg=green> <options=bold>%s</options=bold>.',
                    $oldVersion
                ));
            }
        } catch (\Exception $e) {
            $this->output->writeln(sprintf('Error: <fg=yellow>%s</fg=yellow>', $e->getMessage()));
        }
        $this->output->write(PHP_EOL);
        $this->output->writeln('You can also select update stability using --pre (alpha/beta/rc) or --stable.');
    }

    protected function rollback()
    {
        $updater = new Updater();
        try {
            if ($updater->rollback()) {
                $this->output->writeln('<fg=green>Infection has been rolled back to prior version.</fg=green>');
            } else {
                $this->output->writeln('<fg=red>Rollback failed for reasons unknown.</fg=red>');
            }
        } catch (\Exception $e) {
            $this->output->writeln(sprintf('Error: <fg=yellow>%s</fg=yellow>', $e->getMessage()));
        }
    }

    protected function printAvailableUpdates()
    {
        $this->printCurrentLocalVersion();
        $this->printCurrentStableVersion();
        $this->printCurrentPreReleaseVersion();

        $this->output->writeln('You can select update stability using --pre or --stable when self-updating.');
    }

    protected function printCurrentLocalVersion()
    {
        $this->output->writeln(sprintf(
            'Your current local build version is: <options=bold>%s</options=bold>',
            $this->version
        ));
    }

    protected function printCurrentStableVersion()
    {
        $this->printVersion($this->getStableUpdater());
    }

    protected function printCurrentPreReleaseVersion()
    {
        $this->printVersion($this->getPreReleaseUpdater());
    }

    protected function printVersion(Updater $updater)
    {
        $stability = 'stable';

        if ($updater->getStrategy() instanceof GithubStrategy
            && $updater->getStrategy()->getStability() == GithubStrategy::UNSTABLE
        ) {
            $stability = 'pre-release';
        }

        try {
            if ($updater->hasUpdate()) {
                $this->output->writeln(sprintf(
                    'The current %s build available remotely is: <options=bold>%s</options=bold>',
                    $stability,
                    $updater->getNewVersion()
                ));
            } elseif (false == $updater->getNewVersion()) {
                $this->output->writeln(sprintf('There are no %s builds available.', $stability));
            } else {
                $this->output->writeln(sprintf('You have the current %s build installed.', $stability));
            }
        } catch (\Exception $e) {
            $this->output->writeln(sprintf('Error: <fg=yellow>%s</fg=yellow>', $e->getMessage()));
        }
    }
}
