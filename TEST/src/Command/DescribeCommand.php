<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Command;

use function array_key_exists;
use function array_keys;
use _HumbugBox9658796bb9f0\Infection\Console\IO;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\ProfileList;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputArgument;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Question\Question;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class DescribeCommand extends BaseCommand
{
    protected function configure() : void
    {
        $this->setName('describe')->setDescription('Describes a mutator')->addArgument('Mutator name', InputArgument::OPTIONAL);
    }
    protected function executeCommand(IO $io) : bool
    {
        $mutator = $io->getInput()->getArgument('Mutator name');
        if ($mutator === null) {
            $question = new Question('What mutator do you wish to describe?');
            $question->setAutocompleterValues(array_keys(ProfileList::ALL_MUTATORS));
            $mutator = $io->askQuestion($question);
        }
        if (!array_key_exists($mutator, ProfileList::ALL_MUTATORS)) {
            $io->error(sprintf('"The %s mutator does not exist"', $mutator));
            return \false;
        }
        $mutatorClass = ProfileList::ALL_MUTATORS[$mutator];
        Assert::subclassOf($mutatorClass, Mutator::class);
        $definition = $mutatorClass::getDefinition();
        if ($definition === null) {
            $io->error(sprintf('Mutator "%s" does not have a definition', $mutator));
            return \false;
        }
        $io->writeln('Mutator Category: ' . $definition->getCategory());
        $io->writeln(['', 'Description:']);
        $io->writeln($definition->getDescription());
        $diff = $definition->getDiff();
        $diffColorizer = $this->getApplication()->getContainer()->getDiffColorizer();
        $io->writeln(['', 'For example:', $diffColorizer->colorize($diff)]);
        $remedy = $definition->getRemedies();
        if ($remedy !== null) {
            $io->writeln('');
            $io->writeln($remedy);
        }
        return \true;
    }
}
