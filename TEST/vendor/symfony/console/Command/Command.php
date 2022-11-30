<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Command;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Application;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Attribute\AsCommand;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion\CompletionInput;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion\CompletionSuggestions;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\ExceptionInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\LogicException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\HelperSet;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputArgument;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputDefinition;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputOption;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
class Command
{
    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;
    protected static $defaultName;
    protected static $defaultDescription;
    private $application;
    private $name;
    private $processTitle;
    private $aliases = [];
    private $definition;
    private $hidden = \false;
    private $help = '';
    private $description = '';
    private $fullDefinition;
    private $ignoreValidationErrors = \false;
    private $code;
    private $synopsis = [];
    private $usages = [];
    private $helperSet;
    public static function getDefaultName()
    {
        $class = static::class;
        if (\PHP_VERSION_ID >= 80000 && ($attribute = (new \ReflectionClass($class))->getAttributes(AsCommand::class))) {
            return $attribute[0]->newInstance()->name;
        }
        $r = new \ReflectionProperty($class, 'defaultName');
        return $class === $r->class ? static::$defaultName : null;
    }
    public static function getDefaultDescription() : ?string
    {
        $class = static::class;
        if (\PHP_VERSION_ID >= 80000 && ($attribute = (new \ReflectionClass($class))->getAttributes(AsCommand::class))) {
            return $attribute[0]->newInstance()->description;
        }
        $r = new \ReflectionProperty($class, 'defaultDescription');
        return $class === $r->class ? static::$defaultDescription : null;
    }
    public function __construct(string $name = null)
    {
        $this->definition = new InputDefinition();
        if (null === $name && null !== ($name = static::getDefaultName())) {
            $aliases = \explode('|', $name);
            if ('' === ($name = \array_shift($aliases))) {
                $this->setHidden(\true);
                $name = \array_shift($aliases);
            }
            $this->setAliases($aliases);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if ('' === $this->description) {
            $this->setDescription(static::getDefaultDescription() ?? '');
        }
        $this->configure();
    }
    public function ignoreValidationErrors()
    {
        $this->ignoreValidationErrors = \true;
    }
    public function setApplication(Application $application = null)
    {
        $this->application = $application;
        if ($application) {
            $this->setHelperSet($application->getHelperSet());
        } else {
            $this->helperSet = null;
        }
        $this->fullDefinition = null;
    }
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }
    public function getHelperSet()
    {
        return $this->helperSet;
    }
    public function getApplication()
    {
        return $this->application;
    }
    public function isEnabled()
    {
        return \true;
    }
    protected function configure()
    {
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new LogicException('You must override the execute() method in the concrete command class.');
    }
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->mergeApplicationDefinition();
        try {
            $input->bind($this->getDefinition());
        } catch (ExceptionInterface $e) {
            if (!$this->ignoreValidationErrors) {
                throw $e;
            }
        }
        $this->initialize($input, $output);
        if (null !== $this->processTitle) {
            if (\function_exists('cli_set_process_title')) {
                if (!@\cli_set_process_title($this->processTitle)) {
                    if ('Darwin' === \PHP_OS) {
                        $output->writeln('<comment>Running "cli_set_process_title" as an unprivileged user is not supported on MacOS.</comment>', OutputInterface::VERBOSITY_VERY_VERBOSE);
                    } else {
                        \cli_set_process_title($this->processTitle);
                    }
                }
            } elseif (\function_exists('_HumbugBox9658796bb9f0\\setproctitle')) {
                setproctitle($this->processTitle);
            } elseif (OutputInterface::VERBOSITY_VERY_VERBOSE === $output->getVerbosity()) {
                $output->writeln('<comment>Install the proctitle PECL to be able to change the process title.</comment>');
            }
        }
        if ($input->isInteractive()) {
            $this->interact($input, $output);
        }
        if ($input->hasArgument('command') && null === $input->getArgument('command')) {
            $input->setArgument('command', $this->getName());
        }
        $input->validate();
        if ($this->code) {
            $statusCode = ($this->code)($input, $output);
        } else {
            $statusCode = $this->execute($input, $output);
            if (!\is_int($statusCode)) {
                throw new \TypeError(\sprintf('Return value of "%s::execute()" must be of the type int, "%s" returned.', static::class, \get_debug_type($statusCode)));
            }
        }
        return \is_numeric($statusCode) ? (int) $statusCode : 0;
    }
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions) : void
    {
    }
    public function setCode(callable $code)
    {
        if ($code instanceof \Closure) {
            $r = new \ReflectionFunction($code);
            if (null === $r->getClosureThis()) {
                \set_error_handler(static function () {
                });
                try {
                    if ($c = \Closure::bind($code, $this)) {
                        $code = $c;
                    }
                } finally {
                    \restore_error_handler();
                }
            }
        }
        $this->code = $code;
        return $this;
    }
    public function mergeApplicationDefinition(bool $mergeArgs = \true)
    {
        if (null === $this->application) {
            return;
        }
        $this->fullDefinition = new InputDefinition();
        $this->fullDefinition->setOptions($this->definition->getOptions());
        $this->fullDefinition->addOptions($this->application->getDefinition()->getOptions());
        if ($mergeArgs) {
            $this->fullDefinition->setArguments($this->application->getDefinition()->getArguments());
            $this->fullDefinition->addArguments($this->definition->getArguments());
        } else {
            $this->fullDefinition->setArguments($this->definition->getArguments());
        }
    }
    public function setDefinition($definition)
    {
        if ($definition instanceof InputDefinition) {
            $this->definition = $definition;
        } else {
            $this->definition->setDefinition($definition);
        }
        $this->fullDefinition = null;
        return $this;
    }
    public function getDefinition()
    {
        return $this->fullDefinition ?? $this->getNativeDefinition();
    }
    public function getNativeDefinition()
    {
        if (null === $this->definition) {
            throw new LogicException(\sprintf('Command class "%s" is not correctly initialized. You probably forgot to call the parent constructor.', static::class));
        }
        return $this->definition;
    }
    public function addArgument(string $name, int $mode = null, string $description = '', $default = null)
    {
        $this->definition->addArgument(new InputArgument($name, $mode, $description, $default));
        if (null !== $this->fullDefinition) {
            $this->fullDefinition->addArgument(new InputArgument($name, $mode, $description, $default));
        }
        return $this;
    }
    public function addOption(string $name, $shortcut = null, int $mode = null, string $description = '', $default = null)
    {
        $this->definition->addOption(new InputOption($name, $shortcut, $mode, $description, $default));
        if (null !== $this->fullDefinition) {
            $this->fullDefinition->addOption(new InputOption($name, $shortcut, $mode, $description, $default));
        }
        return $this;
    }
    public function setName(string $name)
    {
        $this->validateName($name);
        $this->name = $name;
        return $this;
    }
    public function setProcessTitle(string $title)
    {
        $this->processTitle = $title;
        return $this;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setHidden(bool $hidden)
    {
        $this->hidden = $hidden;
        return $this;
    }
    public function isHidden()
    {
        return $this->hidden;
    }
    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setHelp(string $help)
    {
        $this->help = $help;
        return $this;
    }
    public function getHelp()
    {
        return $this->help;
    }
    public function getProcessedHelp()
    {
        $name = $this->name;
        $isSingleCommand = $this->application && $this->application->isSingleCommand();
        $placeholders = ['%command.name%', '%command.full_name%'];
        $replacements = [$name, $isSingleCommand ? $_SERVER['PHP_SELF'] : $_SERVER['PHP_SELF'] . ' ' . $name];
        return \str_replace($placeholders, $replacements, $this->getHelp() ?: $this->getDescription());
    }
    public function setAliases(iterable $aliases)
    {
        $list = [];
        foreach ($aliases as $alias) {
            $this->validateName($alias);
            $list[] = $alias;
        }
        $this->aliases = \is_array($aliases) ? $aliases : $list;
        return $this;
    }
    public function getAliases()
    {
        return $this->aliases;
    }
    public function getSynopsis(bool $short = \false)
    {
        $key = $short ? 'short' : 'long';
        if (!isset($this->synopsis[$key])) {
            $this->synopsis[$key] = \trim(\sprintf('%s %s', $this->name, $this->definition->getSynopsis($short)));
        }
        return $this->synopsis[$key];
    }
    public function addUsage(string $usage)
    {
        if (!\str_starts_with($usage, $this->name)) {
            $usage = \sprintf('%s %s', $this->name, $usage);
        }
        $this->usages[] = $usage;
        return $this;
    }
    public function getUsages()
    {
        return $this->usages;
    }
    public function getHelper(string $name)
    {
        if (null === $this->helperSet) {
            throw new LogicException(\sprintf('Cannot retrieve helper "%s" because there is no HelperSet defined. Did you forget to add your command to the application or to set the application on the command using the setApplication() method? You can also set the HelperSet directly using the setHelperSet() method.', $name));
        }
        return $this->helperSet->get($name);
    }
    private function validateName(string $name)
    {
        if (!\preg_match('/^[^\\:]++(\\:[^\\:]++)*$/', $name)) {
            throw new InvalidArgumentException(\sprintf('Command name "%s" is invalid.', $name));
        }
    }
}
