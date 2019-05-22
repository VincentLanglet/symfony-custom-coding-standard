<?php

namespace TwigCS\Report;

use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Human readable output with context.
 */
class TextFormatter
{
    const ERROR_CURSOR_CHAR   = '>>';
    const ERROR_LINE_FORMAT   = '%-5s| %s';
    const ERROR_CONTEXT_LIMIT = 2;
    const ERROR_LINE_WIDTH    = 120;

    /**
     * Input-output helper object.
     *
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function __construct($input, $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @param Report      $report
     * @param string|null $level
     */
    public function display(Report $report, $level = null)
    {
        foreach ($report->getFiles() as $file) {
            $fileMessages = $report->getMessages([
                'file'  => $file,
                'level' => $level,
            ]);

            $this->io->text((count($fileMessages) > 0 ? '<fg=red>KO</fg=red>' : '<info>OK</info>').' '.$file);

            $rows = [];
            foreach ($fileMessages as $message) {
                $lines = $this->getContext(file_get_contents($file), $message->getLine(), $this::ERROR_CONTEXT_LIMIT);

                $formattedText = [];
                foreach ($lines as $no => $code) {
                    $formattedText[] = sprintf($this::ERROR_LINE_FORMAT, $no, wordwrap($code, $this::ERROR_LINE_WIDTH));

                    if ($no === $message->getLine()) {
                        $formattedText[] = sprintf(
                            '<fg=red>'.$this::ERROR_LINE_FORMAT.'</fg=red>',
                            $this::ERROR_CURSOR_CHAR,
                            wordwrap($message->getMessage(), $this::ERROR_LINE_WIDTH)
                        );
                    }
                }

                $rows[] = [
                    new TableCell('<comment>'.$message->getLevelAsString().'</comment>', ['rowspan' => 2]),
                    implode("\n", $formattedText),
                ];
                $rows[] = new TableSeparator();
            }

            $this->io->table([], $rows);
        }

        $summaryString = sprintf(
            'Files linted: %d, notices: %d, warnings: %d, errors: %d',
            $report->getTotalFiles(),
            $report->getTotalNotices(),
            $report->getTotalWarnings(),
            $report->getTotalErrors()
        );

        if (0 === $report->getTotalWarnings() && 0 === $report->getTotalErrors()) {
            $this->io->block($summaryString, 'SUCCESS', 'fg=black;bg=green', ' ', true);
        } elseif (0 < $report->getTotalWarnings() && 0 === $report->getTotalErrors()) {
            $this->io->block($summaryString, 'WARNING', 'fg=black;bg=yellow', ' ', true);
        } else {
            $this->io->block($summaryString, 'ERROR', 'fg=black;bg=red', ' ', true);
        }
    }

    /**
     * @param string $template
     * @param int    $line
     * @param int    $context
     *
     * @return array
     */
    protected function getContext($template, $line, $context)
    {
        $lines = explode("\n", $template);

        $position = max(0, $line - $context);
        $max = min(count($lines), $line - 1 + $context);

        $result = [];
        $indentCount = null;
        while ($position < $max) {
            if (preg_match('/^([\s\t]+)/', $lines[$position], $match)) {
                if (null === $indentCount) {
                    $indentCount = strlen($match[1]);
                }

                if (strlen($match[1]) < $indentCount) {
                    $indentCount = strlen($match[1]);
                }
            } else {
                $indentCount = 0;
            }

            $result[$position + 1] = $lines[$position];
            $position++;
        }

        foreach ($result as $index => $code) {
            $result[$index] = substr($code, $indentCount);
        }

        return $result;
    }
}
