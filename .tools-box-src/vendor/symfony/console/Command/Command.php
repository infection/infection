<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Command;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Application;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Attribute\AsCommand;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Completion\CompletionInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Completion\CompletionSuggestions;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Completion\Suggestion;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\ExceptionInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\LogicException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\HelperSet;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputArgument;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputDefinition;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
class Command
{
    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;
    protected static $defaultName;
    protected static $defaultDescription;
    private ?Application $application = null;
    private ?string $name = null;
    private ?string $processTitle = null;
    private array $aliases = [];
    private InputDefinition $definition;
    private bool $hidden = \false;
    private string $help = '';
    private string $description = '';
    private ?InputDefinition $fullDefinition = null;
    private bool $ignoreValidationErrors = \false;
    private ?\Closure $code = null;
    private array $synopsis = [];
    private array $usages = [];
    private ?HelperSet $helperSet = null;
    public static function getDefaultName() : ?string
    {
        $class = static::class;
        if ($attribute = (new \ReflectionClass($class))->getAttributes(AsCommand::class)) {
            return $attribute[0]->newInstance()->name;
        }
        $r = new \ReflectionProperty($class, 'defaultName');
        if ($class !== $r->class || null === static::$defaultName) {
            return null;
        }
        trigger_deprecation('symfony/console', '6.1', 'Relying on the static property "$defaultName" for setting a command name is deprecated. Add the "%s" attribute to the "%s" class instead.', AsCommand::class, static::class);
        return static::$defaultName;
    }
    public static function getDefaultDescription() : ?string
    {
        $class = static::class;
        if ($attribute = (new \ReflectionClass($class))->getAttributes(AsCommand::class)) {
            return $attribute[0]->newInstance()->description;
        }
        $r = new \ReflectionProperty($class, 'defaultDescription');
        if ($class !== $r->class || null === static::$defaultDescription) {
            return null;
        }
        trigger_deprecation('symfony/console', '6.1', 'Relying on the static property "$defaultDescription" for setting a command description is deprecated. Add the "%s" attribute to the "%s" class instead.', AsCommand::class, static::class);
        return static::$defaultDescription;
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
    public function getHelperSet() : ?HelperSet
    {
        return $this->helperSet;
    }
    public function getApplication() : ?Application
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
    public function run(InputInterface $input, OutputInterface $output) : int
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
            } elseif (\function_exists('_HumbugBoxb47773b41c19\\setproctitle')) {
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
        $definition = $this->getDefinition();
        if (CompletionInput::TYPE_OPTION_VALUE === $input->getCompletionType() && $definition->hasOption($input->getCompletionName())) {
            $definition->getOption($input->getCompletionName())->complete($input, $suggestions);
        } elseif (CompletionInput::TYPE_ARGUMENT_VALUE === $input->getCompletionType() && $definition->hasArgument($input->getCompletionName())) {
            $definition->getArgument($input->getCompletionName())->complete($input, $suggestions);
        }
    }
    public function setCode(callable $code) : static
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
        } else {
            $code = $code(...);
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
    public function setDefinition(array|InputDefinition $definition) : static
    {
        if ($definition instanceof InputDefinition) {
            $this->definition = $definition;
        } else {
            $this->definition->setDefinition($definition);
        }
        $this->fullDefinition = null;
        return $this;
    }
    public function getDefinition() : InputDefinition
    {
        return $this->fullDefinition ?? $this->getNativeDefinition();
    }
    public function getNativeDefinition() : InputDefinition
    {
        return $this->definition ?? throw new LogicException(\sprintf('Command class "%s" is not correctly initialized. You probably forgot to call the parent constructor.', static::class));
    }
    public function addArgument(string $name, int $mode = null, string $description = '', mixed $default = null) : static
    {
        $suggestedValues = 5 <= \func_num_args() ? \func_get_arg(4) : [];
        if (!\is_array($suggestedValues) && !$suggestedValues instanceof \Closure) {
            throw new \TypeError(\sprintf('Argument 5 passed to "%s()" must be array or \\Closure, "%s" given.', __METHOD__, \get_debug_type($suggestedValues)));
        }
        $this->definition->addArgument(new InputArgument($name, $mode, $description, $default, $suggestedValues));
        $this->fullDefinition?->addArgument(new InputArgument($name, $mode, $description, $default, $suggestedValues));
        return $this;
    }
    public function addOption(string $name, string|array $shortcut = null, int $mode = null, string $description = '', mixed $default = null) : static
    {
        $suggestedValues = 6 <= \func_num_args() ? \func_get_arg(5) : [];
        if (!\is_array($suggestedValues) && !$suggestedValues instanceof \Closure) {
            throw new \TypeError(\sprintf('Argument 5 passed to "%s()" must be array or \\Closure, "%s" given.', __METHOD__, \get_debug_type($suggestedValues)));
        }
        $this->definition->addOption(new InputOption($name, $shortcut, $mode, $description, $default, $suggestedValues));
        $this->fullDefinition?->addOption(new InputOption($name, $shortcut, $mode, $description, $default, $suggestedValues));
        return $this;
    }
    public function setName(string $name) : static
    {
        $this->validateName($name);
        $this->name = $name;
        return $this;
    }
    public function setProcessTitle(string $title) : static
    {
        $this->processTitle = $title;
        return $this;
    }
    public function getName() : ?string
    {
        return $this->name;
    }
    public function setHidden(bool $hidden = \true) : static
    {
        $this->hidden = $hidden;
        return $this;
    }
    public function isHidden() : bool
    {
        return $this->hidden;
    }
    public function setDescription(string $description) : static
    {
        $this->description = $description;
        return $this;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function setHelp(string $help) : static
    {
        $this->help = $help;
        return $this;
    }
    public function getHelp() : string
    {
        return $this->help;
    }
    public function getProcessedHelp() : string
    {
        $name = $this->name;
        $isSingleCommand = $this->application?->isSingleCommand();
        $placeholders = ['%command.name%', '%command.full_name%'];
        $replacements = [$name, $isSingleCommand ? $_SERVER['PHP_SELF'] : $_SERVER['PHP_SELF'] . ' ' . $name];
        return \str_replace($placeholders, $replacements, $this->getHelp() ?: $this->getDescription());
    }
    public function setAliases(iterable $aliases) : static
    {
        $list = [];
        foreach ($aliases as $alias) {
            $this->validateName($alias);
            $list[] = $alias;
        }
        $this->aliases = \is_array($aliases) ? $aliases : $list;
        return $this;
    }
    public function getAliases() : array
    {
        return $this->aliases;
    }
    public function getSynopsis(bool $short = \false) : string
    {
        $key = $short ? 'short' : 'long';
        if (!isset($this->synopsis[$key])) {
            $this->synopsis[$key] = \trim(\sprintf('%s %s', $this->name, $this->definition->getSynopsis($short)));
        }
        return $this->synopsis[$key];
    }
    public function addUsage(string $usage) : static
    {
        if (!\str_starts_with($usage, $this->name)) {
            $usage = \sprintf('%s %s', $this->name, $usage);
        }
        $this->usages[] = $usage;
        return $this;
    }
    public function getUsages() : array
    {
        return $this->usages;
    }
    public function getHelper(string $name) : mixed
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
