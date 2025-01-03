<?php

// https://symfony.com/doc/current/console.html#creating-a-command

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'site:serve', aliases: ['serve'])]
class ServeCommand extends Command
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties.
        parent::__construct();

        $this->projectDir = $projectDir;
    }

    protected function configure(): void
    {
        $this
            // the command description shown when running "php bin/console list"
            ->setDescription('Run a simple local server (note: use symfony server:start if you have it)')
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to quickly run a local server...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $host = 'localhost';
        $port = '8000';

        $output->writeln([
            'Built-in PHP development web server started',
            '',
            "CMS:     http://$host:$port/---cms",
            "Website: http://$host:$port",
            '',
            'Press [CTRL + C] or [CMD + .] to exit...',
        ]);

        $process = new Process(['php', '-S', "$host:$port", '-t', $this->projectDir . '/public']);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);
        $process->run();

        // return int(0))
        return Command::SUCCESS;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}