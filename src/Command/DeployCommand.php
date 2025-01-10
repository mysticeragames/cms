<?php

// https://symfony.com/doc/current/console.html#creating-a-command

namespace App\Command;

use App\Services\GitHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'site:deploy')]
class DeployCommand extends Command
{
    private string $projectDir;
    private GitHelper $gitHelper;

    public function __construct(string $projectDir, GitHelper $gitHelper)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties.
        parent::__construct();

        $this->projectDir = $projectDir;
        $this->gitHelper = $gitHelper;
    }

    protected function configure(): void
    {
        $this
            // the command description shown when running "php bin/console list"
            ->setDescription('Deploy the generated files')
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to deploy the generated files...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $rootOutputDir = $this->projectDir . '/content/dist';
        if(!is_dir($rootOutputDir)) {

            // https://symfony.com/doc/current/components/console/helpers/formatterhelper.html
            /** @var FormatterHelper $formatter */
            $formatter = $this->getHelper('formatter');

            $errorMessages = ['Error!', 'Something went wrong'];
            $formattedBlock = $formatter->formatBlock($errorMessages, 'error');
            $output->writeln($formattedBlock);

            $output->writeln("Output directory 'generated' does not exist, create a directory first (mkdir generated) or clone a git submodule");
            
            // return int(1))
            return Command::FAILURE;
        }
        
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Deploying site',
            '==============',
            '',
        ]);

        
        $this->gitHelper->addCommitPush('published');

        
        $output->writeln('--> Done.');


        // return int(0))
        return Command::SUCCESS;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}