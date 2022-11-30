<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Command;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Attribute\AsCommand;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Command\Command;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Completion\CompletionInput;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Completion\CompletionSuggestions;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Style\SymfonyStyle;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Data;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Command\Descriptor\DumpDescriptorInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Command\Descriptor\HtmlDescriptor;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Dumper\CliDumper;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Dumper\HtmlDumper;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Server\DumpServer;
#[AsCommand(name: 'server:dump', description: 'Start a dump server that collects and displays dumps in a single place')]
class ServerDumpCommand extends Command
{
    private DumpServer $server;
    private array $descriptors;
    public function __construct(DumpServer $server, array $descriptors = [])
    {
        $this->server = $server;
        $this->descriptors = $descriptors + ['cli' => new CliDescriptor(new CliDumper()), 'html' => new HtmlDescriptor(new HtmlDumper())];
        parent::__construct();
    }
    protected function configure()
    {
        $this->addOption('format', null, InputOption::VALUE_REQUIRED, \sprintf('The output format (%s)', \implode(', ', $this->getAvailableFormats())), 'cli')->setHelp(<<<'EOF'
<info>%command.name%</info> starts a dump server that collects and displays
dumps in a single place for debugging you application:

  <info>php %command.full_name%</info>

You can consult dumped data in HTML format in your browser by providing the <comment>--format=html</comment> option
and redirecting the output to a file:

  <info>php %command.full_name% --format="html" > dump.html</info>

EOF
);
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        $format = $input->getOption('format');
        if (!($descriptor = $this->descriptors[$format] ?? null)) {
            throw new InvalidArgumentException(\sprintf('Unsupported format "%s".', $format));
        }
        $errorIo = $io->getErrorStyle();
        $errorIo->title('Symfony Var Dumper Server');
        $this->server->start();
        $errorIo->success(\sprintf('Server listening on %s', $this->server->getHost()));
        $errorIo->comment('Quit the server with CONTROL-C.');
        $this->server->listen(function (Data $data, array $context, int $clientId) use($descriptor, $io) {
            $descriptor->describe($io, $data, $context, $clientId);
        });
        return 0;
    }
    public function complete(CompletionInput $input, CompletionSuggestions $suggestions) : void
    {
        if ($input->mustSuggestOptionValuesFor('format')) {
            $suggestions->suggestValues($this->getAvailableFormats());
        }
    }
    private function getAvailableFormats() : array
    {
        return \array_keys($this->descriptors);
    }
}
