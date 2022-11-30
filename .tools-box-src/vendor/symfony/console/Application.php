<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\CompleteCommand;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\DumpCompletionCommand;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\HelpCommand;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\LazyCommand;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\ListCommand;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\SignalableCommandInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Completion\CompletionInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Completion\CompletionSuggestions;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Event\ConsoleCommandEvent;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Event\ConsoleErrorEvent;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Event\ConsoleSignalEvent;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Event\ConsoleTerminateEvent;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\CommandNotFoundException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\ExceptionInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\LogicException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\NamespaceNotFoundException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\RuntimeException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter\OutputFormatter;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\DebugFormatterHelper;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\FormatterHelper;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\Helper;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\HelperSet;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\ProcessHelper;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\QuestionHelper;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\ArgvInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\ArrayInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputArgument;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputAwareInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputDefinition;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\ConsoleOutput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\ConsoleOutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\SignalRegistry\SignalRegistry;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Style\SymfonyStyle;
use _HumbugBoxb47773b41c19\Symfony\Component\ErrorHandler\ErrorHandler;
use _HumbugBoxb47773b41c19\Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use _HumbugBoxb47773b41c19\Symfony\Contracts\Service\ResetInterface;
class Application implements ResetInterface
{
    private array $commands = [];
    private bool $wantHelps = \false;
    private ?Command $runningCommand = null;
    private string $name;
    private string $version;
    private ?CommandLoaderInterface $commandLoader = null;
    private bool $catchExceptions = \true;
    private bool $autoExit = \true;
    private InputDefinition $definition;
    private HelperSet $helperSet;
    private ?EventDispatcherInterface $dispatcher = null;
    private Terminal $terminal;
    private string $defaultCommand;
    private bool $singleCommand = \false;
    private bool $initialized = \false;
    private SignalRegistry $signalRegistry;
    private array $signalsToDispatchEvent = [];
    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        $this->name = $name;
        $this->version = $version;
        $this->terminal = new Terminal();
        $this->defaultCommand = 'list';
        if (\defined('SIGINT') && SignalRegistry::isSupported()) {
            $this->signalRegistry = new SignalRegistry();
            $this->signalsToDispatchEvent = [\SIGINT, \SIGTERM, \SIGUSR1, \SIGUSR2];
        }
    }
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    public function setCommandLoader(CommandLoaderInterface $commandLoader)
    {
        $this->commandLoader = $commandLoader;
    }
    public function getSignalRegistry() : SignalRegistry
    {
        if (!$this->signalRegistry) {
            throw new RuntimeException('Signals are not supported. Make sure that the `pcntl` extension is installed and that "pcntl_*" functions are not disabled by your php.ini\'s "disable_functions" directive.');
        }
        return $this->signalRegistry;
    }
    public function setSignalsToDispatchEvent(int ...$signalsToDispatchEvent)
    {
        $this->signalsToDispatchEvent = $signalsToDispatchEvent;
    }
    public function run(InputInterface $input = null, OutputInterface $output = null) : int
    {
        if (\function_exists('putenv')) {
            @\putenv('LINES=' . $this->terminal->getHeight());
            @\putenv('COLUMNS=' . $this->terminal->getWidth());
        }
        if (null === $input) {
            $input = new ArgvInput();
        }
        if (null === $output) {
            $output = new ConsoleOutput();
        }
        $renderException = function (\Throwable $e) use($output) {
            if ($output instanceof ConsoleOutputInterface) {
                $this->renderThrowable($e, $output->getErrorOutput());
            } else {
                $this->renderThrowable($e, $output);
            }
        };
        if ($phpHandler = \set_exception_handler($renderException)) {
            \restore_exception_handler();
            if (!\is_array($phpHandler) || !$phpHandler[0] instanceof ErrorHandler) {
                $errorHandler = \true;
            } elseif ($errorHandler = $phpHandler[0]->setExceptionHandler($renderException)) {
                $phpHandler[0]->setExceptionHandler($errorHandler);
            }
        }
        $this->configureIO($input, $output);
        try {
            $exitCode = $this->doRun($input, $output);
        } catch (\Exception $e) {
            if (!$this->catchExceptions) {
                throw $e;
            }
            $renderException($e);
            $exitCode = $e->getCode();
            if (\is_numeric($exitCode)) {
                $exitCode = (int) $exitCode;
                if ($exitCode <= 0) {
                    $exitCode = 1;
                }
            } else {
                $exitCode = 1;
            }
        } finally {
            if (!$phpHandler) {
                if (\set_exception_handler($renderException) === $renderException) {
                    \restore_exception_handler();
                }
                \restore_exception_handler();
            } elseif (!$errorHandler) {
                $finalHandler = $phpHandler[0]->setExceptionHandler(null);
                if ($finalHandler !== $renderException) {
                    $phpHandler[0]->setExceptionHandler($finalHandler);
                }
            }
        }
        if ($this->autoExit) {
            if ($exitCode > 255) {
                $exitCode = 255;
            }
            exit($exitCode);
        }
        return $exitCode;
    }
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (\true === $input->hasParameterOption(['--version', '-V'], \true)) {
            $output->writeln($this->getLongVersion());
            return 0;
        }
        try {
            $input->bind($this->getDefinition());
        } catch (ExceptionInterface) {
        }
        $name = $this->getCommandName($input);
        if (\true === $input->hasParameterOption(['--help', '-h'], \true)) {
            if (!$name) {
                $name = 'help';
                $input = new ArrayInput(['command_name' => $this->defaultCommand]);
            } else {
                $this->wantHelps = \true;
            }
        }
        if (!$name) {
            $name = $this->defaultCommand;
            $definition = $this->getDefinition();
            $definition->setArguments(\array_merge($definition->getArguments(), ['command' => new InputArgument('command', InputArgument::OPTIONAL, $definition->getArgument('command')->getDescription(), $name)]));
        }
        try {
            $this->runningCommand = null;
            $command = $this->find($name);
        } catch (\Throwable $e) {
            if (!($e instanceof CommandNotFoundException && !$e instanceof NamespaceNotFoundException) || 1 !== \count($alternatives = $e->getAlternatives()) || !$input->isInteractive()) {
                if (null !== $this->dispatcher) {
                    $event = new ConsoleErrorEvent($input, $output, $e);
                    $this->dispatcher->dispatch($event, ConsoleEvents::ERROR);
                    if (0 === $event->getExitCode()) {
                        return 0;
                    }
                    $e = $event->getError();
                }
                throw $e;
            }
            $alternative = $alternatives[0];
            $style = new SymfonyStyle($input, $output);
            $output->writeln('');
            $formattedBlock = (new FormatterHelper())->formatBlock(\sprintf('Command "%s" is not defined.', $name), 'error', \true);
            $output->writeln($formattedBlock);
            if (!$style->confirm(\sprintf('Do you want to run "%s" instead? ', $alternative), \false)) {
                if (null !== $this->dispatcher) {
                    $event = new ConsoleErrorEvent($input, $output, $e);
                    $this->dispatcher->dispatch($event, ConsoleEvents::ERROR);
                    return $event->getExitCode();
                }
                return 1;
            }
            $command = $this->find($alternative);
        }
        if ($command instanceof LazyCommand) {
            $command = $command->getCommand();
        }
        $this->runningCommand = $command;
        $exitCode = $this->doRunCommand($command, $input, $output);
        $this->runningCommand = null;
        return $exitCode;
    }
    public function reset()
    {
    }
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }
    public function getHelperSet() : HelperSet
    {
        return $this->helperSet ??= $this->getDefaultHelperSet();
    }
    public function setDefinition(InputDefinition $definition)
    {
        $this->definition = $definition;
    }
    public function getDefinition() : InputDefinition
    {
        $this->definition ??= $this->getDefaultInputDefinition();
        if ($this->singleCommand) {
            $inputDefinition = $this->definition;
            $inputDefinition->setArguments();
            return $inputDefinition;
        }
        return $this->definition;
    }
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions) : void
    {
        if (CompletionInput::TYPE_ARGUMENT_VALUE === $input->getCompletionType() && 'command' === $input->getCompletionName()) {
            $commandNames = [];
            foreach ($this->all() as $name => $command) {
                if ($command->isHidden() || $command->getName() !== $name) {
                    continue;
                }
                $commandNames[] = $command->getName();
                foreach ($command->getAliases() as $name) {
                    $commandNames[] = $name;
                }
            }
            $suggestions->suggestValues(\array_filter($commandNames));
            return;
        }
        if (CompletionInput::TYPE_OPTION_NAME === $input->getCompletionType()) {
            $suggestions->suggestOptions($this->getDefinition()->getOptions());
            return;
        }
    }
    public function getHelp() : string
    {
        return $this->getLongVersion();
    }
    public function areExceptionsCaught() : bool
    {
        return $this->catchExceptions;
    }
    public function setCatchExceptions(bool $boolean)
    {
        $this->catchExceptions = $boolean;
    }
    public function isAutoExitEnabled() : bool
    {
        return $this->autoExit;
    }
    public function setAutoExit(bool $boolean)
    {
        $this->autoExit = $boolean;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function setName(string $name)
    {
        $this->name = $name;
    }
    public function getVersion() : string
    {
        return $this->version;
    }
    public function setVersion(string $version)
    {
        $this->version = $version;
    }
    public function getLongVersion()
    {
        if ('UNKNOWN' !== $this->getName()) {
            if ('UNKNOWN' !== $this->getVersion()) {
                return \sprintf('%s <info>%s</info>', $this->getName(), $this->getVersion());
            }
            return $this->getName();
        }
        return 'Console Tool';
    }
    public function register(string $name) : Command
    {
        return $this->add(new Command($name));
    }
    public function addCommands(array $commands)
    {
        foreach ($commands as $command) {
            $this->add($command);
        }
    }
    public function add(Command $command)
    {
        $this->init();
        $command->setApplication($this);
        if (!$command->isEnabled()) {
            $command->setApplication(null);
            return null;
        }
        if (!$command instanceof LazyCommand) {
            $command->getDefinition();
        }
        if (!$command->getName()) {
            throw new LogicException(\sprintf('The command defined in "%s" cannot have an empty name.', \get_debug_type($command)));
        }
        $this->commands[$command->getName()] = $command;
        foreach ($command->getAliases() as $alias) {
            $this->commands[$alias] = $command;
        }
        return $command;
    }
    public function get(string $name)
    {
        $this->init();
        if (!$this->has($name)) {
            throw new CommandNotFoundException(\sprintf('The command "%s" does not exist.', $name));
        }
        if (!isset($this->commands[$name])) {
            throw new CommandNotFoundException(\sprintf('The "%s" command cannot be found because it is registered under multiple names. Make sure you don\'t set a different name via constructor or "setName()".', $name));
        }
        $command = $this->commands[$name];
        if ($this->wantHelps) {
            $this->wantHelps = \false;
            $helpCommand = $this->get('help');
            $helpCommand->setCommand($command);
            return $helpCommand;
        }
        return $command;
    }
    public function has(string $name) : bool
    {
        $this->init();
        return isset($this->commands[$name]) || $this->commandLoader?->has($name) && $this->add($this->commandLoader->get($name));
    }
    public function getNamespaces() : array
    {
        $namespaces = [];
        foreach ($this->all() as $command) {
            if ($command->isHidden()) {
                continue;
            }
            $namespaces[] = $this->extractAllNamespaces($command->getName());
            foreach ($command->getAliases() as $alias) {
                $namespaces[] = $this->extractAllNamespaces($alias);
            }
        }
        return \array_values(\array_unique(\array_filter(\array_merge([], ...$namespaces))));
    }
    public function findNamespace(string $namespace) : string
    {
        $allNamespaces = $this->getNamespaces();
        $expr = \implode('[^:]*:', \array_map('preg_quote', \explode(':', $namespace))) . '[^:]*';
        $namespaces = \preg_grep('{^' . $expr . '}', $allNamespaces);
        if (empty($namespaces)) {
            $message = \sprintf('There are no commands defined in the "%s" namespace.', $namespace);
            if ($alternatives = $this->findAlternatives($namespace, $allNamespaces)) {
                if (1 == \count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }
                $message .= \implode("\n    ", $alternatives);
            }
            throw new NamespaceNotFoundException($message, $alternatives);
        }
        $exact = \in_array($namespace, $namespaces, \true);
        if (\count($namespaces) > 1 && !$exact) {
            throw new NamespaceNotFoundException(\sprintf("The namespace \"%s\" is ambiguous.\nDid you mean one of these?\n%s.", $namespace, $this->getAbbreviationSuggestions(\array_values($namespaces))), \array_values($namespaces));
        }
        return $exact ? $namespace : \reset($namespaces);
    }
    public function find(string $name)
    {
        $this->init();
        $aliases = [];
        foreach ($this->commands as $command) {
            foreach ($command->getAliases() as $alias) {
                if (!$this->has($alias)) {
                    $this->commands[$alias] = $command;
                }
            }
        }
        if ($this->has($name)) {
            return $this->get($name);
        }
        $allCommands = $this->commandLoader ? \array_merge($this->commandLoader->getNames(), \array_keys($this->commands)) : \array_keys($this->commands);
        $expr = \implode('[^:]*:', \array_map('preg_quote', \explode(':', $name))) . '[^:]*';
        $commands = \preg_grep('{^' . $expr . '}', $allCommands);
        if (empty($commands)) {
            $commands = \preg_grep('{^' . $expr . '}i', $allCommands);
        }
        if (empty($commands) || \count(\preg_grep('{^' . $expr . '$}i', $commands)) < 1) {
            if (\false !== ($pos = \strrpos($name, ':'))) {
                $this->findNamespace(\substr($name, 0, $pos));
            }
            $message = \sprintf('Command "%s" is not defined.', $name);
            if ($alternatives = $this->findAlternatives($name, $allCommands)) {
                $alternatives = \array_filter($alternatives, function ($name) {
                    return !$this->get($name)->isHidden();
                });
                if (1 == \count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }
                $message .= \implode("\n    ", $alternatives);
            }
            throw new CommandNotFoundException($message, \array_values($alternatives));
        }
        if (\count($commands) > 1) {
            $commandList = $this->commandLoader ? \array_merge(\array_flip($this->commandLoader->getNames()), $this->commands) : $this->commands;
            $commands = \array_unique(\array_filter($commands, function ($nameOrAlias) use(&$commandList, $commands, &$aliases) {
                if (!$commandList[$nameOrAlias] instanceof Command) {
                    $commandList[$nameOrAlias] = $this->commandLoader->get($nameOrAlias);
                }
                $commandName = $commandList[$nameOrAlias]->getName();
                $aliases[$nameOrAlias] = $commandName;
                return $commandName === $nameOrAlias || !\in_array($commandName, $commands);
            }));
        }
        if (\count($commands) > 1) {
            $usableWidth = $this->terminal->getWidth() - 10;
            $abbrevs = \array_values($commands);
            $maxLen = 0;
            foreach ($abbrevs as $abbrev) {
                $maxLen = \max(Helper::width($abbrev), $maxLen);
            }
            $abbrevs = \array_map(function ($cmd) use($commandList, $usableWidth, $maxLen, &$commands) {
                if ($commandList[$cmd]->isHidden()) {
                    unset($commands[\array_search($cmd, $commands)]);
                    return \false;
                }
                $abbrev = \str_pad($cmd, $maxLen, ' ') . ' ' . $commandList[$cmd]->getDescription();
                return Helper::width($abbrev) > $usableWidth ? Helper::substr($abbrev, 0, $usableWidth - 3) . '...' : $abbrev;
            }, \array_values($commands));
            if (\count($commands) > 1) {
                $suggestions = $this->getAbbreviationSuggestions(\array_filter($abbrevs));
                throw new CommandNotFoundException(\sprintf("Command \"%s\" is ambiguous.\nDid you mean one of these?\n%s.", $name, $suggestions), \array_values($commands));
            }
        }
        $command = $this->get(\reset($commands));
        if ($command->isHidden()) {
            throw new CommandNotFoundException(\sprintf('The command "%s" does not exist.', $name));
        }
        return $command;
    }
    public function all(string $namespace = null)
    {
        $this->init();
        if (null === $namespace) {
            if (!$this->commandLoader) {
                return $this->commands;
            }
            $commands = $this->commands;
            foreach ($this->commandLoader->getNames() as $name) {
                if (!isset($commands[$name]) && $this->has($name)) {
                    $commands[$name] = $this->get($name);
                }
            }
            return $commands;
        }
        $commands = [];
        foreach ($this->commands as $name => $command) {
            if ($namespace === $this->extractNamespace($name, \substr_count($namespace, ':') + 1)) {
                $commands[$name] = $command;
            }
        }
        if ($this->commandLoader) {
            foreach ($this->commandLoader->getNames() as $name) {
                if (!isset($commands[$name]) && $namespace === $this->extractNamespace($name, \substr_count($namespace, ':') + 1) && $this->has($name)) {
                    $commands[$name] = $this->get($name);
                }
            }
        }
        return $commands;
    }
    public static function getAbbreviations(array $names) : array
    {
        $abbrevs = [];
        foreach ($names as $name) {
            for ($len = \strlen($name); $len > 0; --$len) {
                $abbrev = \substr($name, 0, $len);
                $abbrevs[$abbrev][] = $name;
            }
        }
        return $abbrevs;
    }
    public function renderThrowable(\Throwable $e, OutputInterface $output) : void
    {
        $output->writeln('', OutputInterface::VERBOSITY_QUIET);
        $this->doRenderThrowable($e, $output);
        if (null !== $this->runningCommand) {
            $output->writeln(\sprintf('<info>%s</info>', OutputFormatter::escape(\sprintf($this->runningCommand->getSynopsis(), $this->getName()))), OutputInterface::VERBOSITY_QUIET);
            $output->writeln('', OutputInterface::VERBOSITY_QUIET);
        }
    }
    protected function doRenderThrowable(\Throwable $e, OutputInterface $output) : void
    {
        do {
            $message = \trim($e->getMessage());
            if ('' === $message || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $class = \get_debug_type($e);
                $title = \sprintf('  [%s%s]  ', $class, 0 !== ($code = $e->getCode()) ? ' (' . $code . ')' : '');
                $len = Helper::width($title);
            } else {
                $len = 0;
            }
            if (\str_contains($message, "@anonymous\x00")) {
                $message = \preg_replace_callback('/[a-zA-Z_\\x7f-\\xff][\\\\a-zA-Z0-9_\\x7f-\\xff]*+@anonymous\\x00.*?\\.php(?:0x?|:[0-9]++\\$)[0-9a-fA-F]++/', function ($m) {
                    return \class_exists($m[0], \false) ? ((\get_parent_class($m[0]) ?: \key(\class_implements($m[0]))) ?: 'class') . '@anonymous' : $m[0];
                }, $message);
            }
            $width = $this->terminal->getWidth() ? $this->terminal->getWidth() - 1 : \PHP_INT_MAX;
            $lines = [];
            foreach ('' !== $message ? \preg_split('/\\r?\\n/', $message) : [] as $line) {
                foreach ($this->splitStringByWidth($line, $width - 4) as $line) {
                    $lineLength = Helper::width($line) + 4;
                    $lines[] = [$line, $lineLength];
                    $len = \max($lineLength, $len);
                }
            }
            $messages = [];
            if (!$e instanceof ExceptionInterface || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $messages[] = \sprintf('<comment>%s</comment>', OutputFormatter::escape(\sprintf('In %s line %s:', \basename($e->getFile()) ?: 'n/a', $e->getLine() ?: 'n/a')));
            }
            $messages[] = $emptyLine = \sprintf('<error>%s</error>', \str_repeat(' ', $len));
            if ('' === $message || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $messages[] = \sprintf('<error>%s%s</error>', $title, \str_repeat(' ', \max(0, $len - Helper::width($title))));
            }
            foreach ($lines as $line) {
                $messages[] = \sprintf('<error>  %s  %s</error>', OutputFormatter::escape($line[0]), \str_repeat(' ', $len - $line[1]));
            }
            $messages[] = $emptyLine;
            $messages[] = '';
            $output->writeln($messages, OutputInterface::VERBOSITY_QUIET);
            if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $output->writeln('<comment>Exception trace:</comment>', OutputInterface::VERBOSITY_QUIET);
                $trace = $e->getTrace();
                \array_unshift($trace, ['function' => '', 'file' => $e->getFile() ?: 'n/a', 'line' => $e->getLine() ?: 'n/a', 'args' => []]);
                for ($i = 0, $count = \count($trace); $i < $count; ++$i) {
                    $class = $trace[$i]['class'] ?? '';
                    $type = $trace[$i]['type'] ?? '';
                    $function = $trace[$i]['function'] ?? '';
                    $file = $trace[$i]['file'] ?? 'n/a';
                    $line = $trace[$i]['line'] ?? 'n/a';
                    $output->writeln(\sprintf(' %s%s at <info>%s:%s</info>', $class, $function ? $type . $function . '()' : '', $file, $line), OutputInterface::VERBOSITY_QUIET);
                }
                $output->writeln('', OutputInterface::VERBOSITY_QUIET);
            }
        } while ($e = $e->getPrevious());
    }
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        if (\true === $input->hasParameterOption(['--ansi'], \true)) {
            $output->setDecorated(\true);
        } elseif (\true === $input->hasParameterOption(['--no-ansi'], \true)) {
            $output->setDecorated(\false);
        }
        if (\true === $input->hasParameterOption(['--no-interaction', '-n'], \true)) {
            $input->setInteractive(\false);
        }
        switch ($shellVerbosity = (int) \getenv('SHELL_VERBOSITY')) {
            case -1:
                $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
                break;
            case 1:
                $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                break;
            case 2:
                $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
                break;
            case 3:
                $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
                break;
            default:
                $shellVerbosity = 0;
                break;
        }
        if (\true === $input->hasParameterOption(['--quiet', '-q'], \true)) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            $shellVerbosity = -1;
        } else {
            if ($input->hasParameterOption('-vvv', \true) || $input->hasParameterOption('--verbose=3', \true) || 3 === $input->getParameterOption('--verbose', \false, \true)) {
                $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
                $shellVerbosity = 3;
            } elseif ($input->hasParameterOption('-vv', \true) || $input->hasParameterOption('--verbose=2', \true) || 2 === $input->getParameterOption('--verbose', \false, \true)) {
                $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
                $shellVerbosity = 2;
            } elseif ($input->hasParameterOption('-v', \true) || $input->hasParameterOption('--verbose=1', \true) || $input->hasParameterOption('--verbose', \true) || $input->getParameterOption('--verbose', \false, \true)) {
                $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                $shellVerbosity = 1;
            }
        }
        if (-1 === $shellVerbosity) {
            $input->setInteractive(\false);
        }
        if (\function_exists('putenv')) {
            @\putenv('SHELL_VERBOSITY=' . $shellVerbosity);
        }
        $_ENV['SHELL_VERBOSITY'] = $shellVerbosity;
        $_SERVER['SHELL_VERBOSITY'] = $shellVerbosity;
    }
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        foreach ($command->getHelperSet() as $helper) {
            if ($helper instanceof InputAwareInterface) {
                $helper->setInput($input);
            }
        }
        if ($this->signalsToDispatchEvent) {
            $commandSignals = $command instanceof SignalableCommandInterface ? $command->getSubscribedSignals() : [];
            if ($commandSignals || null !== $this->dispatcher) {
                if (!$this->signalRegistry) {
                    throw new RuntimeException('Unable to subscribe to signal events. Make sure that the `pcntl` extension is installed and that "pcntl_*" functions are not disabled by your php.ini\'s "disable_functions" directive.');
                }
                if (Terminal::hasSttyAvailable()) {
                    $sttyMode = \shell_exec('stty -g');
                    foreach ([\SIGINT, \SIGTERM] as $signal) {
                        $this->signalRegistry->register($signal, static function () use($sttyMode) {
                            \shell_exec('stty ' . $sttyMode);
                        });
                    }
                }
                foreach ($commandSignals as $signal) {
                    $this->signalRegistry->register($signal, [$command, 'handleSignal']);
                }
            }
            if (null !== $this->dispatcher) {
                foreach ($this->signalsToDispatchEvent as $signal) {
                    $event = new ConsoleSignalEvent($command, $input, $output, $signal);
                    $this->signalRegistry->register($signal, function ($signal, $hasNext) use($event) {
                        $this->dispatcher->dispatch($event, ConsoleEvents::SIGNAL);
                        if (!$hasNext) {
                            if (!\in_array($signal, [\SIGUSR1, \SIGUSR2], \true)) {
                                exit(0);
                            }
                        }
                    });
                }
            }
        }
        if (null === $this->dispatcher) {
            return $command->run($input, $output);
        }
        try {
            $command->mergeApplicationDefinition();
            $input->bind($command->getDefinition());
        } catch (ExceptionInterface) {
        }
        $event = new ConsoleCommandEvent($command, $input, $output);
        $e = null;
        try {
            $this->dispatcher->dispatch($event, ConsoleEvents::COMMAND);
            if ($event->commandShouldRun()) {
                $exitCode = $command->run($input, $output);
            } else {
                $exitCode = ConsoleCommandEvent::RETURN_CODE_DISABLED;
            }
        } catch (\Throwable $e) {
            $event = new ConsoleErrorEvent($input, $output, $e, $command);
            $this->dispatcher->dispatch($event, ConsoleEvents::ERROR);
            $e = $event->getError();
            if (0 === ($exitCode = $event->getExitCode())) {
                $e = null;
            }
        }
        $event = new ConsoleTerminateEvent($command, $input, $output, $exitCode);
        $this->dispatcher->dispatch($event, ConsoleEvents::TERMINATE);
        if (null !== $e) {
            throw $e;
        }
        return $event->getExitCode();
    }
    protected function getCommandName(InputInterface $input) : ?string
    {
        return $this->singleCommand ? $this->defaultCommand : $input->getFirstArgument();
    }
    protected function getDefaultInputDefinition() : InputDefinition
    {
        return new InputDefinition([new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'), new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display help for the given command. When no command is given display help for the <info>' . $this->defaultCommand . '</info> command'), new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'), new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'), new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'), new InputOption('--ansi', '', InputOption::VALUE_NEGATABLE, 'Force (or disable --no-ansi) ANSI output', null), new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question')]);
    }
    protected function getDefaultCommands() : array
    {
        return [new HelpCommand(), new ListCommand(), new CompleteCommand(), new DumpCompletionCommand()];
    }
    protected function getDefaultHelperSet() : HelperSet
    {
        return new HelperSet([new FormatterHelper(), new DebugFormatterHelper(), new ProcessHelper(), new QuestionHelper()]);
    }
    private function getAbbreviationSuggestions(array $abbrevs) : string
    {
        return '    ' . \implode("\n    ", $abbrevs);
    }
    public function extractNamespace(string $name, int $limit = null) : string
    {
        $parts = \explode(':', $name, -1);
        return \implode(':', null === $limit ? $parts : \array_slice($parts, 0, $limit));
    }
    private function findAlternatives(string $name, iterable $collection) : array
    {
        $threshold = 1000.0;
        $alternatives = [];
        $collectionParts = [];
        foreach ($collection as $item) {
            $collectionParts[$item] = \explode(':', $item);
        }
        foreach (\explode(':', $name) as $i => $subname) {
            foreach ($collectionParts as $collectionName => $parts) {
                $exists = isset($alternatives[$collectionName]);
                if (!isset($parts[$i]) && $exists) {
                    $alternatives[$collectionName] += $threshold;
                    continue;
                } elseif (!isset($parts[$i])) {
                    continue;
                }
                $lev = \levenshtein($subname, $parts[$i]);
                if ($lev <= \strlen($subname) / 3 || '' !== $subname && \str_contains($parts[$i], $subname)) {
                    $alternatives[$collectionName] = $exists ? $alternatives[$collectionName] + $lev : $lev;
                } elseif ($exists) {
                    $alternatives[$collectionName] += $threshold;
                }
            }
        }
        foreach ($collection as $item) {
            $lev = \levenshtein($name, $item);
            if ($lev <= \strlen($name) / 3 || \str_contains($item, $name)) {
                $alternatives[$item] = isset($alternatives[$item]) ? $alternatives[$item] - $lev : $lev;
            }
        }
        $alternatives = \array_filter($alternatives, function ($lev) use($threshold) {
            return $lev < 2 * $threshold;
        });
        \ksort($alternatives, \SORT_NATURAL | \SORT_FLAG_CASE);
        return \array_keys($alternatives);
    }
    public function setDefaultCommand(string $commandName, bool $isSingleCommand = \false) : static
    {
        $this->defaultCommand = \explode('|', \ltrim($commandName, '|'))[0];
        if ($isSingleCommand) {
            $this->find($commandName);
            $this->singleCommand = \true;
        }
        return $this;
    }
    public function isSingleCommand() : bool
    {
        return $this->singleCommand;
    }
    private function splitStringByWidth(string $string, int $width) : array
    {
        if (\false === ($encoding = \mb_detect_encoding($string, null, \true))) {
            return \str_split($string, $width);
        }
        $utf8String = \mb_convert_encoding($string, 'utf8', $encoding);
        $lines = [];
        $line = '';
        $offset = 0;
        while (\preg_match('/.{1,10000}/u', $utf8String, $m, 0, $offset)) {
            $offset += \strlen($m[0]);
            foreach (\preg_split('//u', $m[0]) as $char) {
                if (\mb_strwidth($line . $char, 'utf8') <= $width) {
                    $line .= $char;
                    continue;
                }
                $lines[] = \str_pad($line, $width);
                $line = $char;
            }
        }
        $lines[] = \count($lines) ? \str_pad($line, $width) : $line;
        \mb_convert_variables($encoding, 'utf8', $lines);
        return $lines;
    }
    private function extractAllNamespaces(string $name) : array
    {
        $parts = \explode(':', $name, -1);
        $namespaces = [];
        foreach ($parts as $part) {
            if (\count($namespaces)) {
                $namespaces[] = \end($namespaces) . ':' . $part;
            } else {
                $namespaces[] = $part;
            }
        }
        return $namespaces;
    }
    private function init()
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = \true;
        foreach ($this->getDefaultCommands() as $command) {
            $this->add($command);
        }
    }
}
