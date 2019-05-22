<?php

namespace TwigCS;

use Twig\Environment;
use Twig\Error\Error;
use Twig\Source;
use TwigCS\Extension\SniffsExtension;
use TwigCS\Report\Report;
use TwigCS\Report\SniffViolation;
use TwigCS\Ruleset\Ruleset;
use TwigCS\Sniff\PostParserSniffInterface;
use TwigCS\Sniff\PreParserSniffInterface;
use TwigCS\Sniff\SniffInterface;
use TwigCS\Token\TokenizerInterface;

/**
 * Linter is the main class and will process twig files against a set of rules.
 */
class Linter
{
    /**
     * @var Environment
     */
    protected $env;

    /**
     * @var SniffsExtension
     */
    protected $sniffsExtension;

    /**
     * @var TokenizerInterface
     */
    protected $tokenizer;

    /**
     * @param Environment        $env
     * @param TokenizerInterface $tokenizer
     */
    public function __construct(Environment $env, TokenizerInterface $tokenizer)
    {
        $this->env = $env;
        $this->sniffsExtension = $this->env->getExtension('TwigCS\Extension\SniffsExtension');
        $this->tokenizer = $tokenizer;
    }

    /**
     * Run the linter on the given $files against the given $ruleset.
     *
     * @param array|string $files   List of files to process.
     * @param Ruleset      $ruleset Set of rules to check.
     *
     * @return Report an object with all violations and stats.
     */
    public function run($files, Ruleset $ruleset)
    {
        if (!is_array($files) && !$files instanceof \Traversable) {
            $files = [$files];
        }

        if (empty($files)) {
            throw new \Exception('No files to process, provide at least one file to be linted');
        }

        // setUp
        $report = new Report();
        set_error_handler(function ($type, $msg) use ($report) {
            if (E_USER_DEPRECATED === $type) {
                $sniffViolation = new SniffViolation(
                    SniffInterface::MESSAGE_TYPE_NOTICE,
                    $msg,
                    '',
                    ''
                );

                $report->addMessage($sniffViolation);
            }
        });

        foreach ($ruleset->getSniffs() as $sniff) {
            if ($sniff instanceof PostParserSniffInterface) {
                $this->sniffsExtension->addSniff($sniff);
            }

            $sniff->enable($report);
        }

        // Process
        foreach ($files as $file) {
            $this->processTemplate($file, $ruleset, $report);

            // Add this file to the report.
            $report->addFile($file);
        }

        // tearDown
        restore_error_handler();
        foreach ($ruleset->getSniffs() as $sniff) {
            if ($sniff instanceof PostParserSniffInterface) {
                $this->sniffsExtension->removeSniff($sniff);
            }

            $sniff->disable();
        }

        return $report;
    }

    /**
     * Checks one template against the set of rules.
     *
     * @param string  $file    File to check as a string.
     * @param Ruleset $ruleset Set of rules to check.
     * @param Report  $report  Current report to fill.
     *
     * @return bool
     */
    public function processTemplate($file, $ruleset, $report)
    {
        $twigSource = new Source(file_get_contents($file), $file, $file);

        // Tokenize + Parse.
        try {
            $this->env->parse($this->env->tokenize($twigSource));
        } catch (Error $e) {
            $sniffViolation = new SniffViolation(
                SniffInterface::MESSAGE_TYPE_ERROR,
                $e->getRawMessage(),
                $e->getTemplateLine(),
                $e->getSourceContext()->getName()
            );

            $report->addMessage($sniffViolation);

            return false;
        }

        // Tokenizer.
        try {
            $stream = $this->tokenizer->tokenize($twigSource);
        } catch (\Exception $e) {
            $sniffViolation = new SniffViolation(
                SniffInterface::MESSAGE_TYPE_ERROR,
                sprintf('Unable to tokenize file "%s"', (string) $file),
                '',
                (string) $file
            );

            $report->addMessage($sniffViolation);

            return false;
        }

        /** @var PreParserSniffInterface[] $sniffs */
        $sniffs = $ruleset->getSniffs(SniffInterface::TYPE_PRE_PARSER);
        foreach ($sniffs as $sniff) {
            foreach ($stream as $index => $token) {
                $sniff->process($token, $index, $stream);
            }
        }

        return true;
    }
}
