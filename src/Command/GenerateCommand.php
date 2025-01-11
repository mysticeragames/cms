<?php

// https://symfony.com/doc/current/console.html#creating-a-command

// src/Command/GenerateCommand.php
namespace App\Command;

use App\Repositories\PageRepository;
use App\Services\ContentRenderer;
use App\Services\TwigRenderer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'site:generate')]
class GenerateCommand extends Command
{
    private PageRepository $pageRepository;
    private TwigRenderer $twigRenderer;
    private ContentRenderer $contentRenderer;

    private string $projectDir;

    public function __construct(PageRepository $pageRepository, TwigRenderer $twigRenderer, ContentRenderer $contentRenderer, string $projectDir)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        //$this->requirePassword = $requirePassword;

        parent::__construct();

        $this->pageRepository = $pageRepository;
        $this->twigRenderer = $twigRenderer;
        $this->contentRenderer = $contentRenderer;
        $this->projectDir = $projectDir;
    }

    protected function configure(): void
    {
        $this
            // the command description shown when running "php bin/console list"
            ->setDescription('Generates static html files')
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to generate a static site...')
        ;

        // $this
        //     // ...
        //     ->addArgument('password', $this->requirePassword ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'User password')
        // ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }


        $rootOutputDir = $this->projectDir . '/content/dist';
        if (!is_dir($rootOutputDir)) {
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
            'Generating site',
            '===============',
            '',
        ]);

        // the value returned by someMethod() can be an iterator (https://php.net/iterator)
        // that generates and returns the messages with the 'yield' PHP keyword
        //$output->writeln($this->someMethod());

        // outputs a message followed by a "\n"

        // outputs a message without adding a "\n" at the end of the line
        $output->writeln('Processing');

        $pages = $this->pageRepository->getPages();

        $section1 = $output->section();
        $section2 = $output->section();

        $total = count($pages);

        for ($i = 0; $i < $total; $i++) {
            $page = $pages[$i];
            $path = $page['path'];
            $outputPath = $rootOutputDir . '/' . $path . '.html';
            $outputDir = dirname($rootOutputDir);

            $section1->overwrite(($i + 1) . "/$total");
            $section2->overwrite($path);


            $content = $this->contentRenderer->render($this->projectDir, $path);
            //$content = $this->twigRenderer->render($path);

            //$response = $this->renderController->renderUrl($path);

            // Create directory if it does not exist
            if (!is_dir($outputDir)) {
                mkdir($outputDir, recursive: true);
            }

            // Write file
            file_put_contents($outputPath, $content);
        }
        $section2->overwrite('--> Done.');


        // return int(0))
        return Command::SUCCESS;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}
