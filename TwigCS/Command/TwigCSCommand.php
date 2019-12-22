<?php

namespace TwigCS\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TwigCS\Config\Config;
use TwigCS\Environment\StubbedEnvironment;
use TwigCS\Report\TextFormatter;
use TwigCS\Ruleset\Ruleset;
use TwigCS\Runner\Linter;
use TwigCS\Token\Tokenizer;

/**
 * TwigCS stands for "Twig Code Sniffer" and will check twig template of your project.
 * This is heavily inspired by the symfony lint command and PHP_CodeSniffer tool
 *
 * @see https://github.com/squizlabs/PHP_CodeSniffer
 */
class TwigCSCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('lint')
            ->setDescription('Lints a template and outputs encountered errors')
            ->setDefinition([
                new InputOption(
                    'exclude',
                    'e',
                    InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                    'Excludes, based on regex, paths of files and folders from parsing',
                    ['vendor/']
                ),
                new InputOption(
                    'level',
                    'l',
                    InputOption::VALUE_OPTIONAL,
                    'Allowed values are notice, warning or error',
                    'notice'
                ),
                new InputOption(
                    'working-dir',
                    'd',
                    InputOption::VALUE_OPTIONAL,
                    'Run as if this was started in <working-dir> instead of the current working directory',
                    getcwd()
                ),
                new InputOption(
                    'fix',
                    'f',
                    InputOption::VALUE_NONE,
                    'Automatically fix all the fixable violations'
                ),
            ])
            ->addArgument(
                'paths',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Paths of files and folders to parse',
                null
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getArgument('paths');
        $exclude = $input->getOption('exclude');
        $level = $input->getOption('level');
        $currentDir = $input->getOption('working-dir');
        $fix = $input->getOption('fix');

        $config = new Config([
            'paths'            => $paths,
            'exclude'          => $exclude,
            'workingDirectory' => $currentDir,
        ]);

        // Get the rules to apply.
        $ruleset = new Ruleset();
        $ruleset->addStandard();

        // Execute the linter.
        $twig = new StubbedEnvironment();
        $linter = new Linter($twig, new Tokenizer($twig));
        $report = $linter->run($config->findFiles(), $ruleset, $fix);

        // Format the output.
        $reporter = new TextFormatter($input, $output);
        $reporter->display($report, $level);

        // Return a meaningful error code.
        if ($report->getTotalErrors()) {
            return 1;
        }

        return 0;
    }
}
