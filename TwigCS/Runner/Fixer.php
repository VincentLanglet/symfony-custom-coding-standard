<?php

namespace TwigCS\Runner;

use Exception;
use Twig\Source;
use TwigCS\Ruleset\Ruleset;
use TwigCS\Token\Token;
use TwigCS\Token\Tokenizer;

/**
 * Class Fixer
 */
class Fixer
{
    /**
     * The number of times we have looped over a file.
     *
     * @var int
     */
    protected $loops = 0;

    /**
     * The character used when we are adding new lines.
     *
     * @var string
     */
    protected $eolChar = "\n";

    /**
     * @var Ruleset
     */
    protected $ruleset = null;

    /**
     * @var Tokenizer
     */
    protected $tokenizer = null;

    /**
     * The list of tokens that make up the file contents.
     *
     * This is a simplified list which just contains the token content and nothing
     * else. This is the array that is updated as fixes are made, not the file's
     * token array. Imploding this array will give you the file content back.
     *
     * @var array<int, string>
     */
    protected $tokens = [];

    /**
     * A list of tokens that have already been fixed.
     *
     * We don't allow the same token to be fixed more than once each time
     * through a file as this can easily cause conflicts between sniffs.
     *
     * @var int[]
     */
    protected $fixedTokens = [];

    /**
     * The last value of each fixed token.
     *
     * If a token is being "fixed" back to its last value, the fix is
     * probably conflicting with another.
     *
     * @var array<int, string>
     */
    protected $oldTokenValues = [];

    /**
     * A list of tokens that have been fixed during a changeset.
     *
     * All changes in changeset must be able to be applied, or else
     * the entire changeset is rejected.
     *
     * @var array
     */
    protected $changeset = [];

    /**
     * Is there an open changeset.
     *
     * @var bool
     */
    protected $inChangeset = false;

    /**
     * Is the current fixing loop in conflict?
     *
     * @var bool
     */
    protected $inConflict = false;

    /**
     * The number of fixes that have been performed.
     *
     * @var int
     */
    protected $numFixes = 0;

    /**
     * Starts fixing a new file.
     *
     * @param Ruleset   $ruleset
     * @param Tokenizer $tokenizer
     *
     * @return void
     */
    public function __construct(Ruleset $ruleset, Tokenizer $tokenizer)
    {
        $this->ruleset = $ruleset;
        $this->tokenizer = $tokenizer;
    }

    /**
     * @param array $tokens
     */
    public function startFile(array $tokens)
    {
        $this->numFixes = 0;
        $this->fixedTokens = [];

        $this->tokens = array_map(function (Token $token) {
            return $token->getValue();
        }, $tokens);

        if (preg_match("/\r\n?|\n/", $this->getContents(), $matches) !== 1) {
            // Assume there are no newlines.
            $this->eolChar = "\n";
        } else {
            $this->eolChar = $matches[0];
        }
    }

    /**
     * Attempt to fix the file by processing it until no fixes are made.
     *
     * @param string $file
     *
     * @return bool
     */
    public function fixFile(string $file)
    {
        $contents = file_get_contents($file);

        $this->loops = 0;
        while ($this->loops < 50) {
            $this->inConflict = false;

            try {
                $twigSource = new Source($contents, 'TwigCS');
                $stream = $this->tokenizer->tokenize($twigSource);
            } catch (Exception $e) {
                return false;
            }

            $this->startFile($stream);

            $sniffs = $this->ruleset->getSniffs();
            foreach ($sniffs as $sniff) {
                $sniff->processFile($stream);
            }

            $this->loops++;

            if (0 === $this->numFixes && !$this->inConflict) {
                // Nothing left to do.
                break;
            }

            // Only needed once file content has changed.
            $contents = $this->getContents();
        }

        if ($this->numFixes > 0) {
            return false;
        }

        return true;
    }

    /**
     * Generates a text diff of the original file and the new content.
     *
     * @param string $filePath File path to diff the file against.
     *
     * @return string
     */
    public function generateDiff($filePath)
    {
        $cwd = getcwd().DIRECTORY_SEPARATOR;
        if (strpos($filePath, $cwd) === 0) {
            $filename = substr($filePath, strlen($cwd));
        } else {
            $filename = $filePath;
        }

        $contents = $this->getContents();

        $tempName  = tempnam(sys_get_temp_dir(), 'phpcs-fixer');
        $fixedFile = fopen($tempName, 'w');
        fwrite($fixedFile, $contents);

        // We must use something like shell_exec() because whitespace at the end
        // of lines is critical to diff files.
        $filename = escapeshellarg($filename);
        $cmd      = "diff -u -L$filename -LPHP_CodeSniffer $filename \"$tempName\"";

        $diff = shell_exec($cmd);

        fclose($fixedFile);
        if (is_file($tempName) === true) {
            unlink($tempName);
        }

        $diffLines = explode(PHP_EOL, $diff);
        if (count($diffLines) === 1) {
            // Seems to be required for cygwin.
            $diffLines = explode("\n", $diff);
        }

        $diff = [];
        foreach ($diffLines as $line) {
            if (isset($line[0]) === true) {
                switch ($line[0]) {
                    case '-':
                        $diff[] = "\033[31m$line\033[0m";
                        break;
                    case '+':
                        $diff[] = "\033[32m$line\033[0m";
                        break;
                    default:
                        $diff[] = $line;
                }
            }
        }

        $diff = implode(PHP_EOL, $diff);

        return $diff;
    }

    /**
     * Get the current content of the file, as a string.
     *
     * @return string
     */
    public function getContents()
    {
        $contents = implode($this->tokens);

        return $contents;
    }

    /**
     * Get the current fixed content of a token.
     *
     * This function takes changesets into account so should be used
     * instead of directly accessing the token array.
     *
     * @param int $tokenPosition The position of the token in the token stack.
     *
     * @return string
     */
    public function getTokenContent($tokenPosition)
    {
        if ($this->inChangeset && isset($this->changeset[$tokenPosition])) {
            return $this->changeset[$tokenPosition];
        }

        return $this->tokens[$tokenPosition];
    }

    /**
     * Start recording actions for a changeset.
     */
    public function beginChangeset()
    {
        if ($this->inConflict) {
            return;
        }

        $this->changeset   = [];
        $this->inChangeset = true;
    }

    /**
     * Stop recording actions for a changeset, and apply logged changes.
     */
    public function endChangeset()
    {
        if ($this->inConflict) {
            return;
        }

        $this->inChangeset = false;

        $success = true;
        $applied = [];
        foreach ($this->changeset as $tokenPosition => $content) {
            $success = $this->replaceToken($tokenPosition, $content);
            if (!$success) {
                break;
            } else {
                $applied[] = $tokenPosition;
            }
        }

        if (!$success) {
            // Rolling back all changes.
            foreach ($applied as $tokenPosition) {
                $this->revertToken($tokenPosition);
            }
        }

        $this->changeset = [];
    }

    /**
     * Stop recording actions for a changeset, and discard logged changes.
     *
     * @return void
     */
    public function rollbackChangeset()
    {
        $this->inChangeset = false;
        $this->inConflict = false;

        if (count($this->changeset) > 0) {
            $this->changeset = [];
        }
    }

    /**
     * Replace the entire contents of a token.
     *
     * @param int    $tokenPosition The position of the token in the token stack.
     * @param string $content       The new content of the token.
     *
     * @return bool If the change was accepted.
     */
    public function replaceToken($tokenPosition, $content)
    {
        if ($this->inConflict) {
            return false;
        }

        if (!$this->inChangeset && isset($this->fixedTokens[$tokenPosition])) {
            return false;
        }

        if ($this->inChangeset) {
            $this->changeset[$tokenPosition] = $content;

            return true;
        }

        if (!isset($this->oldTokenValues[$tokenPosition])) {
            $this->oldTokenValues[$tokenPosition] = [
                'curr' => $content,
                'prev' => $this->tokens[$tokenPosition],
                'loop' => $this->loops,
            ];
        } else {
            if ($content === $this->oldTokenValues[$tokenPosition]['prev']
                && ($this->loops - 1) === $this->oldTokenValues[$tokenPosition]['loop']
            ) {
                if ($this->oldTokenValues[$tokenPosition]['loop'] >= ($this->loops - 1)) {
                    $this->inConflict = true;
                }

                return false;
            }

            $this->oldTokenValues[$tokenPosition]['prev'] = $this->oldTokenValues[$tokenPosition]['curr'];
            $this->oldTokenValues[$tokenPosition]['curr'] = $content;
            $this->oldTokenValues[$tokenPosition]['loop'] = $this->loops;
        }

        $this->fixedTokens[$tokenPosition] = $this->tokens[$tokenPosition];
        $this->tokens[$tokenPosition] = $content;
        $this->numFixes++;

        return true;
    }

    /**
     * Reverts the previous fix made to a token.
     *
     * @param int $tokenPosition The position of the token in the token stack.
     *
     * @return bool If a change was reverted.
     */
    public function revertToken($tokenPosition)
    {
        if (!isset($this->fixedTokens[$tokenPosition])) {
            return false;
        }

        $this->tokens[$tokenPosition] = $this->fixedTokens[$tokenPosition];
        unset($this->fixedTokens[$tokenPosition]);
        $this->numFixes--;

        return true;
    }

    /**
     * Adds a newline to end of a token's content.
     *
     * @param int $tokenPosition The position of the token in the token stack.
     *
     * @return bool If the change was accepted.
     */
    public function addNewline($tokenPosition)
    {
        $current = $this->getTokenContent($tokenPosition);

        return $this->replaceToken($tokenPosition, $current.$this->eolChar);
    }

    /**
     * Adds a newline to the start of a token's content.
     *
     * @param int $tokenPosition The position of the token in the token stack.
     *
     * @return bool If the change was accepted.
     */
    public function addNewlineBefore($tokenPosition)
    {
        $current = $this->getTokenContent($tokenPosition);

        return $this->replaceToken($tokenPosition, $this->eolChar.$current);
    }

    /**
     * Adds content to the end of a token's current content.
     *
     * @param int    $tokenPosition The position of the token in the token stack.
     * @param string $content       The content to add.
     *
     * @return bool If the change was accepted.
     */
    public function addContent($tokenPosition, $content)
    {
        $current = $this->getTokenContent($tokenPosition);

        return $this->replaceToken($tokenPosition, $current.$content);
    }

    /**
     * Adds content to the start of a token's current content.
     *
     * @param int    $tokenPosition The position of the token in the token stack.
     * @param string $content       The content to add.
     *
     * @return bool If the change was accepted.
     */
    public function addContentBefore($tokenPosition, $content)
    {
        $current = $this->getTokenContent($tokenPosition);

        return $this->replaceToken($tokenPosition, $content.$current);
    }
}
