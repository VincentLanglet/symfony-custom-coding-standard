<?php

namespace TwigCS\Command;

use \Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Loader\ArrayLoader;
use TwigCS\Config\Config;
use TwigCS\Environment\StubbedEnvironment;
use TwigCS\Linter;
use TwigCS\Report\TextFormatter;
use TwigCS\Ruleset\RulesetFactory;
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
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'Excludes, based on regex, paths of files and folders from parsing',
                    ['vendor/']
                ),
                new InputOption(
                    'level',
                    'l',
                    InputOption::VALUE_OPTIONAL,
                    'Allowed values are: warning, error',
                    'warning'
                ),
                new InputOption(
                    'working-dir',
                    'd',
                    InputOption::VALUE_OPTIONAL,
                    'Run as if this was started in <working-dir> instead of the current working directory',
                    getcwd()
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
        $paths      = $input->getArgument('paths');
        $exclude    = $input->getOption('exclude');
        $level      = $input->getOption('level');
        $currentDir = $input->getOption('working-dir');

        $config = new Config([
            'paths'            => $paths,
            'exclude'          => $exclude,
            'workingDirectory' => $currentDir,
        ]);

        $twig     = new StubbedEnvironment(new ArrayLoader());
        $linter   = new Linter($twig, new Tokenizer($twig));
        $factory  = new RulesetFactory();
        $reporter = new TextFormatter($input, $output);
        $exitCode = 0;

        // Get the rules to apply.
        $ruleset = $factory->createStandardRuleset();

        // Execute the linter.
        $report = $linter->run($config->findFiles(), $ruleset);

        // Format the output.
        $reporter->display($report, $level);

        // Return a meaningful error code.
        if ($report->getTotalErrors()) {
            $exitCode = 1;
        }

        return $exitCode;
    }
}
