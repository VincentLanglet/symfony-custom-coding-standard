<?php

namespace SymfonyCustom\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * A test to ensure that arrays conform to the array coding standard.
 */
class ArrayDeclarationSniff implements Sniff
{
    /**
     * The number of spaces code should be indented.
     *
     * @var int
     */
    public $indent = 4;

    /**
     * Allow or disallow a multiline assignment
     * By default, it is allowed to avoid conflict with a maximum line length rule
     *
     * [
     *     'key' =>
     *         'value'
     * ]
     *
     * @var bool
     */
    public $ignoreNewLines = true;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
            T_ARRAY,
            T_OPEN_SHORT_ARRAY,
        );
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The current file being checked.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (T_ARRAY === $tokens[$stackPtr]['code']) {
            $phpcsFile->recordMetric($stackPtr, 'Short array syntax used', 'no');

            // Array keyword should be lower case.
            if (strtolower($tokens[$stackPtr]['content']) !== $tokens[$stackPtr]['content']) {
                if (strtoupper($tokens[$stackPtr]['content']) === $tokens[$stackPtr]['content']) {
                    $phpcsFile->recordMetric($stackPtr, 'Array keyword case', 'upper');
                } else {
                    $phpcsFile->recordMetric($stackPtr, 'Array keyword case', 'mixed');
                }

                $error = 'Array keyword should be lower case; expected "array" but found "%s"';
                $data  = [$tokens[$stackPtr]['content']];
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'NotLowerCase', $data);

                if (true === $fix) {
                    $phpcsFile->fixer->replaceToken($stackPtr, 'array');
                }
            } else {
                $phpcsFile->recordMetric($stackPtr, 'Array keyword case', 'lower');
            }

            $arrayStart = $tokens[$stackPtr]['parenthesis_opener'];
            if (isset($tokens[$arrayStart]['parenthesis_closer']) === false) {
                return;
            }

            $arrayEnd = $tokens[$arrayStart]['parenthesis_closer'];

            if (($stackPtr + 1) !== $arrayStart) {
                $error = 'There must be no space between the "array" keyword and the opening parenthesis';
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterKeyword');

                if (true === $fix) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($stackPtr + 1); $i < $arrayStart; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                }
            }
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Short array syntax used', 'yes');
            $arrayStart = $stackPtr;
            $arrayEnd   = $tokens[$stackPtr]['bracket_closer'];
        }

        // Check for empty arrays.
        $content = $phpcsFile->findNext(T_WHITESPACE, ($arrayStart + 1), ($arrayEnd + 1), true);
        if ($content === $arrayEnd) {
            // Empty array, but if the brackets aren't together, there's a problem.
            if (($arrayEnd - $arrayStart) !== 1) {
                $error = 'Empty array declaration must have no space between the parentheses';
                $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceInEmptyArray');

                if (true === $fix) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $phpcsFile->fixer->endChangeset();
                }
            }

            // We can return here because there is nothing else to check.
            // All code below can assume that the array is not empty.
            return;
        }

        if ($tokens[$arrayStart]['line'] === $tokens[$arrayEnd]['line']) {
            $this->processSingleLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd);
        } else {
            $this->processMultiLineArray($phpcsFile, $stackPtr, $arrayStart, $arrayEnd);
        }
    }

    /**
     * Processes a single-line array definition.
     *
     * @param File $phpcsFile  The current file being checked.
     * @param int  $stackPtr   The position of the current token in the stack passed in $tokens.
     * @param int  $arrayStart The token that starts the array definition.
     * @param int  $arrayEnd   The token that ends the array definition.
     *
     * @return void
     */
    public function processSingleLineArray(File $phpcsFile, $stackPtr, $arrayStart, $arrayEnd)
    {
        $tokens = $phpcsFile->getTokens();

        // Check if there are multiple values. If so, then it has to be multiple lines
        // unless it is contained inside a function call or condition.
        $valueCount = 0;
        $commas     = array();
        for ($i = ($arrayStart + 1); $i < $arrayEnd; $i++) {
            // Skip bracketed statements, like function calls.
            if (T_OPEN_PARENTHESIS === $tokens[$i]['code']) {
                $i = $tokens[$i]['parenthesis_closer'];
                continue;
            }

            if (T_COMMA === $tokens[$i]['code']) {
                // Before counting this comma, make sure we are not at the end of the array.
                $next = $phpcsFile->findNext(T_WHITESPACE, ($i + 1), $arrayEnd, true);
                if (false !== $next) {
                    $valueCount++;
                    $commas[] = $i;
                } else {
                    // There is a comma at the end of a single line array.
                    $error = 'Comma not allowed after last value in single-line array declaration';
                    $fix   = $phpcsFile->addFixableError($error, $i, 'CommaAfterLast');
                    if (true === $fix) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }
                }
            }
        }

        // Now check each of the double arrows (if any).
        $nextArrow = $arrayStart;
        while (($nextArrow = $phpcsFile->findNext(T_DOUBLE_ARROW, ($nextArrow + 1), $arrayEnd)) !== false) {
            if (T_WHITESPACE !== $tokens[($nextArrow - 1)]['code']) {
                $content = $tokens[($nextArrow - 1)]['content'];
                $error   = 'Expected 1 space between "%s" and double arrow; 0 found';
                $data    = array($content);
                $fix     = $phpcsFile->addFixableError($error, $nextArrow, 'NoSpaceBeforeDoubleArrow', $data);
                if (true === $fix) {
                    $phpcsFile->fixer->addContentBefore($nextArrow, ' ');
                }
            } else {
                $spaceLength = $tokens[($nextArrow - 1)]['length'];
                if (1 !== $spaceLength) {
                    $content = $tokens[($nextArrow - 2)]['content'];
                    $error   = 'Expected 1 space between "%s" and double arrow; %s found';
                    $data    = array($content, $spaceLength);

                    $fix = $phpcsFile->addFixableError($error, $nextArrow, 'SpaceBeforeDoubleArrow', $data);
                    if (true === $fix) {
                        $phpcsFile->fixer->replaceToken(($nextArrow - 1), ' ');
                    }
                }
            }

            if (T_WHITESPACE !== $tokens[($nextArrow + 1)]['code']) {
                $content = $tokens[($nextArrow + 1)]['content'];
                $error   = 'Expected 1 space between double arrow and "%s"; 0 found';
                $data    = array($content);

                $fix     = $phpcsFile->addFixableError($error, $nextArrow, 'NoSpaceAfterDoubleArrow', $data);
                if (true === $fix) {
                    $phpcsFile->fixer->addContent($nextArrow, ' ');
                }
            } else {
                $spaceLength = $tokens[($nextArrow + 1)]['length'];
                if (1 !== $spaceLength) {
                    $content = $tokens[($nextArrow + 2)]['content'];
                    $error   = 'Expected 1 space between double arrow and "%s"; %s found';
                    $data    = array($content, $spaceLength);

                    $fix = $phpcsFile->addFixableError($error, $nextArrow, 'SpaceAfterDoubleArrow', $data);
                    if (true === $fix) {
                        $phpcsFile->fixer->replaceToken(($nextArrow + 1), ' ');
                    }
                }
            }
        }

        if ($valueCount > 0) {
            // We have a multiple value array
            foreach ($commas as $comma) {
                if (T_WHITESPACE !== $tokens[($comma + 1)]['code']) {
                    $content = $tokens[($comma + 1)]['content'];
                    $error = 'Expected 1 space between comma and "%s"; 0 found';
                    $data = [$content];

                    $fix = $phpcsFile->addFixableError($error, $comma, 'NoSpaceAfterComma', $data);
                    if (true === $fix) {
                        $phpcsFile->fixer->addContent($comma, ' ');
                    }
                } else {
                    $spaceLength = $tokens[($comma + 1)]['length'];
                    if (1 !== $spaceLength) {
                        $content = $tokens[($comma + 2)]['content'];
                        $error = 'Expected 1 space between comma and "%s"; %s found';
                        $data = [$content, $spaceLength];

                        $fix = $phpcsFile->addFixableError($error, $comma, 'SpaceAfterComma', $data);
                        if (true === $fix) {
                            $phpcsFile->fixer->replaceToken(($comma + 1), ' ');
                        }
                    }
                }

                if (T_WHITESPACE === $tokens[($comma - 1)]['code']) {
                    $content = $tokens[($comma - 2)]['content'];
                    $spaceLength = $tokens[($comma - 1)]['length'];
                    $error = 'Expected 0 spaces between "%s" and comma; %s found';
                    $data = [$content, $spaceLength];

                    $fix = $phpcsFile->addFixableError($error, $comma, 'SpaceBeforeComma', $data);
                    if (true === $fix) {
                        $phpcsFile->fixer->replaceToken(($comma - 1), '');
                    }
                }
            }
        }
    }

    /**
     * Processes a multi-line array definition.
     *
     * @param File $phpcsFile  The current file being checked.
     * @param int  $stackPtr   The position of the current token in the stack passed in $tokens.
     * @param int  $arrayStart The token that starts the array definition.
     * @param int  $arrayEnd   The token that ends the array definition.
     *
     * @return void
     */
    public function processMultiLineArray(File $phpcsFile, $stackPtr, $arrayStart, $arrayEnd)
    {
        $tokens = $phpcsFile->getTokens();

        $indent = $phpcsFile->findFirstOnLine(T_WHITESPACE, $arrayStart);
        if (false === $indent) {
            $currentIndent = 0;
        } else {
            $currentIndent = mb_strlen($tokens[$indent]['content']);
        }

        // Check the closing bracket is on a new line.
        $lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($arrayEnd - 1), $arrayStart, true);
        if ($tokens[$lastContent]['line'] === $tokens[$arrayEnd]['line']) {
            $error = 'Closing parenthesis of array declaration must be on a new line';
            $fix   = $phpcsFile->addFixableError($error, $arrayEnd, 'CloseBraceNewLine');
            if (true === $fix) {
                $phpcsFile->fixer->addNewlineBefore($arrayEnd);
            }
        } elseif ($tokens[$arrayEnd]['column'] !== $currentIndent + 1) {
            // Check the closing bracket is lined up under the "a" in array.
            $expected = ($currentIndent);
            $found    = ($tokens[$arrayEnd]['column'] - 1);
            $error    = 'Closing parenthesis not aligned correctly; expected %s space(s) but found %s';
            $data     = array($expected, $found);

            $fix = $phpcsFile->addFixableError($error, $arrayEnd, 'CloseBraceNotAligned', $data);
            if (true === $fix) {
                if (0 === $found) {
                    $phpcsFile->fixer->addContent(($arrayEnd - 1), str_repeat(' ', $expected));
                } else {
                    $phpcsFile->fixer->replaceToken(($arrayEnd - 1), str_repeat(' ', $expected));
                }
            }
        }

        $keyUsed    = false;
        $singleUsed = false;
        $indices    = array();
        $maxLength  = 0;

        if (T_ARRAY === $tokens[$stackPtr]['code']) {
            $lastToken = $tokens[$stackPtr]['parenthesis_opener'];
        } else {
            $lastToken = $stackPtr;
        }

        // Find all the double arrows that reside in this scope.
        for ($nextToken = ($stackPtr + 1); $nextToken < $arrayEnd; $nextToken++) {
            // Skip bracketed statements, like function calls.
            if (T_OPEN_PARENTHESIS === $tokens[$nextToken]['code']
                && (false === isset($tokens[$nextToken]['parenthesis_owner'])
                || $tokens[$nextToken]['parenthesis_owner'] !== $stackPtr)
            ) {
                $nextToken = $tokens[$nextToken]['parenthesis_closer'];
                continue;
            }

            if (T_ARRAY === $tokens[$nextToken]['code']
                || T_OPEN_SHORT_ARRAY === $tokens[$nextToken]['code']
                || T_CLOSURE === $tokens[$nextToken]['code']
            ) {
                // Let subsequent calls of this test handle nested arrays.
                if (T_DOUBLE_ARROW !== $tokens[$lastToken]['code']) {
                    $indices[] = array('value' => $nextToken);
                    $lastToken = $nextToken;
                }

                if (T_ARRAY === $tokens[$nextToken]['code']) {
                    $nextToken = $tokens[$tokens[$nextToken]['parenthesis_opener']]['parenthesis_closer'];
                } elseif (T_OPEN_SHORT_ARRAY === $tokens[$nextToken]['code']) {
                    $nextToken = $tokens[$nextToken]['bracket_closer'];
                } else {
                    // T_CLOSURE.
                    $nextToken = $tokens[$nextToken]['scope_closer'];
                }

                $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
                if (T_COMMA !== $tokens[$nextToken]['code']) {
                    $nextToken--;
                } else {
                    $lastToken = $nextToken;
                }

                continue;
            }

            if (T_DOUBLE_ARROW !== $tokens[$nextToken]['code']
                && T_COMMA !== $tokens[$nextToken]['code']
            ) {
                continue;
            }

            $currentEntry = array();

            if (T_COMMA === $tokens[$nextToken]['code']) {
                $stackPtrCount = 0;
                if (true === isset($tokens[$stackPtr]['nested_parenthesis'])) {
                    $stackPtrCount = count($tokens[$stackPtr]['nested_parenthesis']);
                }

                $commaCount = 0;
                if (true === isset($tokens[$nextToken]['nested_parenthesis'])) {
                    $commaCount = count($tokens[$nextToken]['nested_parenthesis']);
                    if (T_ARRAY === $tokens[$stackPtr]['code']) {
                        // Remove parenthesis that are used to define the array.
                        $commaCount--;
                    }
                }

                if ($commaCount > $stackPtrCount) {
                    // This comma is inside more parenthesis than the ARRAY keyword,
                    // then there it is actually a comma used to separate arguments
                    // in a function call.
                    continue;
                }

                if (true === $keyUsed && T_COMMA === $tokens[$lastToken]['code']) {
                    $error = 'No key specified for array entry; first entry specifies key';
                    $phpcsFile->addError($error, $nextToken, 'NoKeySpecified');

                    return;
                }

                if (false === $keyUsed) {
                    if (T_WHITESPACE === $tokens[($nextToken - 1)]['code']) {
                        $content = $tokens[($nextToken - 2)]['content'];
                        if ($tokens[($nextToken - 1)]['content'] === $phpcsFile->eolChar) {
                            $spaceLength = 'newline';
                        } else {
                            $spaceLength = $tokens[($nextToken - 1)]['length'];
                        }

                        $error = 'Expected 0 spaces between "%s" and comma; %s found';
                        $data  = array(
                            $content,
                            $spaceLength,
                        );

                        $fix = $phpcsFile->addFixableError($error, $nextToken, 'SpaceBeforeComma', $data);
                        if (true === $fix) {
                            $phpcsFile->fixer->replaceToken(($nextToken - 1), '');
                        }
                    }

                    $valueContent = $phpcsFile->findNext(
                        Tokens::$emptyTokens,
                        ($lastToken + 1),
                        $nextToken,
                        true
                    );

                    $indices[]  = array('value' => $valueContent);
                    $singleUsed = true;
                }

                $lastToken = $nextToken;
                continue;
            }

            if (T_DOUBLE_ARROW === $tokens[$nextToken]['code']) {
                if (true === $singleUsed) {
                    $error = 'Key specified for array entry; first entry has no key';
                    $phpcsFile->addError($error, $nextToken, 'KeySpecified');

                    return;
                }

                $currentEntry['arrow'] = $nextToken;
                $keyUsed = true;

                // Find the start of index that uses this double arrow.
                $indexEnd   = $phpcsFile->findPrevious(T_WHITESPACE, ($nextToken - 1), $arrayStart, true);
                $indexStart = $phpcsFile->findStartOfStatement($indexEnd);

                if ($indexStart === $indexEnd) {
                    $currentEntry['index']         = $indexEnd;
                    $currentEntry['index_content'] = $tokens[$indexEnd]['content'];
                } else {
                    $currentEntry['index']         = $indexStart;
                    $currentEntry['index_content'] = $phpcsFile->getTokensAsString(
                        $indexStart,
                        ($indexEnd - $indexStart + 1)
                    );
                }

                $indexLength = mb_strlen($currentEntry['index_content']);
                if ($maxLength < $indexLength) {
                    $maxLength = $indexLength;
                }

                // Find the value of this index.
                $nextContent = $phpcsFile->findNext(
                    Tokens::$emptyTokens,
                    ($nextToken + 1),
                    $arrayEnd,
                    true
                );

                $currentEntry['value'] = $nextContent;
                $indices[] = $currentEntry;
                $lastToken = $nextToken;
            }
        }

        /*
            This section checks for arrays that don't specify keys.

            Arrays such as:
               array(
                   'aaa',
                   'bbb',
                   'd',
               );
        */

        if (false === $keyUsed && false === empty($indices)) {
            $count     = count($indices);
            $lastIndex = $indices[($count - 1)]['value'];

            $trailingContent = $phpcsFile->findPrevious(
                Tokens::$emptyTokens,
                ($arrayEnd - 1),
                $lastIndex,
                true
            );

            if (T_COMMA !== $tokens[$trailingContent]['code']) {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'no');
                $error = 'Comma required after last value in array declaration';
                $fix   = $phpcsFile->addFixableError($error, $trailingContent, 'NoCommaAfterLast');
                if (true === $fix) {
                    $phpcsFile->fixer->addContent($trailingContent, ',');
                }
            } else {
                $phpcsFile->recordMetric($stackPtr, 'Array end comma', 'yes');
            }

            $lastValueLine = false;
            foreach ($indices as $value) {
                if (true === empty($value['value'])) {
                    // Array was malformed and we couldn't figure out
                    // the array value correctly, so we have to ignore it.
                    // Other parts of this sniff will correct the error.
                    continue;
                }

                if (false !== $lastValueLine && $tokens[$value['value']]['line'] === $lastValueLine) {
                    $error = 'Each value in a multi-line array must be on a new line';
                    $fix   = $phpcsFile->addFixableError($error, $value['value'], 'ValueNoNewline');
                    if (true === $fix) {
                        if (T_WHITESPACE === $tokens[($value['value'] - 1)]['code']) {
                            $phpcsFile->fixer->replaceToken(($value['value'] - 1), '');
                        }

                        $phpcsFile->fixer->addNewlineBefore($value['value']);
                    }
                } elseif (T_WHITESPACE === $tokens[($value['value'] - 1)]['code']) {
                    $expected = $currentIndent + $this->indent;

                    $first = $phpcsFile->findFirstOnLine(T_WHITESPACE, $value['value'], true);
                    $found = ($tokens[$first]['column'] - 1);
                    if ($found !== $expected) {
                        $error = 'Array value not aligned correctly; expected %s spaces but found %s';
                        $data  = array($expected, $found);

                        $fix = $phpcsFile->addFixableError($error, $value['value'], 'ValueNotAligned', $data);
                        if (true === $fix) {
                            if (0 === $found) {
                                $phpcsFile->fixer->addContent(($value['value'] - 1), str_repeat(' ', $expected));
                            } else {
                                $phpcsFile->fixer->replaceToken(($value['value'] - 1), str_repeat(' ', $expected));
                            }
                        }
                    }
                }

                $lastValueLine = $tokens[$value['value']]['line'];
            }
        }

        /*
            Below the actual indentation of the array is checked.
            Errors will be thrown when a key is not aligned, when
            a double arrow is not aligned, and when a value is not
            aligned correctly.
            If an error is found in one of the above areas, then errors
            are not reported for the rest of the line to avoid reporting
            spaces and columns incorrectly. Often fixing the first
            problem will fix the other 2 anyway.

            For example:

            $a = array(
                  'index'  => '2',
                 );

            or

            $a = [
                  'index'  => '2',
                 ];

            In this array, the double arrow is indented too far, but this
            will also cause an error in the value's alignment. If the arrow were
            to be moved back one space however, then both errors would be fixed.
        */

        $numValues = count($indices);

        $indicesStart  = ($currentIndent + $this->indent + 1);
        $arrowStart    = ($indicesStart + $maxLength + 1);
        $valueStart    = ($arrowStart + 3);
        $indexLine     = $tokens[$stackPtr]['line'];
        $lastIndexLine = null;
        foreach ($indices as $index) {
            if (isset($index['index']) === false) {
                // Array value only.
                if ($tokens[$index['value']]['line'] === $tokens[$stackPtr]['line'] && $numValues > 1) {
                    $error = 'The first value in a multi-value array must be on a new line';
                    $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'FirstValueNoNewline');
                    if (true === $fix) {
                        $phpcsFile->fixer->addNewlineBefore($index['value']);
                    }
                }

                continue;
            }

            $lastIndexLine = $indexLine;
            $indexLine     = $tokens[$index['index']]['line'];

            if ($indexLine === $tokens[$stackPtr]['line']) {
                $error = 'The first index in a multi-value array must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $index['index'], 'FirstIndexNoNewline');
                if (true === $fix) {
                    $phpcsFile->fixer->addNewlineBefore($index['index']);
                }

                continue;
            }

            if ($indexLine === $lastIndexLine) {
                $error = 'Each index in a multi-line array must be on a new line';
                $fix   = $phpcsFile->addFixableError($error, $index['index'], 'IndexNoNewline');
                if (true === $fix) {
                    if (T_WHITESPACE === $tokens[($index['index'] - 1)]['code']) {
                        $phpcsFile->fixer->replaceToken(($index['index'] - 1), '');
                    }

                    $phpcsFile->fixer->addNewlineBefore($index['index']);
                }

                continue;
            }

            if ($indicesStart !== $tokens[$index['index']]['column']) {
                $expected = ($indicesStart - 1);
                $found    = ($tokens[$index['index']]['column'] - 1);
                $error    = 'Array key not aligned correctly; expected %s spaces but found %s';
                $data     = array($expected, $found);

                $fix = $phpcsFile->addFixableError($error, $index['index'], 'KeyNotAligned', $data);

                if (true === $fix) {
                    if (0 === $found) {
                        $phpcsFile->fixer->addContent(($index['index'] - 1), str_repeat(' ', $expected));
                    } else {
                        $phpcsFile->fixer->replaceToken(($index['index'] - 1), str_repeat(' ', $expected));
                    }
                }

                continue;
            }

            if ($tokens[$index['arrow']]['column'] !== $arrowStart) {
                $expected = ($arrowStart - (mb_strlen($index['index_content']) + $tokens[$index['index']]['column']));
                $found    = $tokens[$index['arrow']]['column']
                    - (mb_strlen($index['index_content']) + $tokens[$index['index']]['column']);

                if ($found < 0) {
                    $found = 'newline';
                }

                $error    = 'Array double arrow not aligned correctly; expected %s space(s) but found %s';
                $data     = array($expected, $found);

                if ('newline' !== $found || false === $this->ignoreNewLines) {
                    $fix = $phpcsFile->addFixableError($error, $index['arrow'], 'DoubleArrowNotAligned', $data);
                    if (true === $fix) {
                        if ('newline' === $found) {
                            $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($index['value'] - 1), null, true);
                            $phpcsFile->fixer->beginChangeset();
                            for ($i = ($prev + 1); $i < $index['value']; $i++) {
                                $phpcsFile->fixer->replaceToken($i, '');
                            }

                            $phpcsFile->fixer->replaceToken(($index['value'] - 1), str_repeat(' ', $expected));
                            $phpcsFile->fixer->endChangeset();
                        } elseif (0 === $found) {
                            $phpcsFile->fixer->addContent(($index['arrow'] - 1), str_repeat(' ', $expected));
                        } else {
                            $phpcsFile->fixer->replaceToken(($index['arrow'] - 1), str_repeat(' ', $expected));
                        }
                    }
                }

                continue;
            }

            if ($tokens[$index['value']]['column'] !== $valueStart) {
                $expected = ($valueStart - ($tokens[$index['arrow']]['length'] + $tokens[$index['arrow']]['column']));
                $found    = $tokens[$index['value']]['column']
                    - ($tokens[$index['arrow']]['length'] + $tokens[$index['arrow']]['column']);

                if ($found < 0) {
                    $found = 'newline';
                }

                if ('newline' !== $found || false === $this->ignoreNewLines) {
                    $error = 'Array value not aligned correctly; expected %s space(s) but found %s';
                    $data = [
                        $expected,
                        $found,
                    ];

                    $fix = $phpcsFile->addFixableError($error, $index['arrow'], 'ValueNotAligned', $data);
                    if (true === $fix) {
                        if ('newline' === $found) {
                            $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($index['value'] - 1), null, true);
                            $phpcsFile->fixer->beginChangeset();
                            for ($i = ($prev + 1); $i < $index['value']; $i++) {
                                $phpcsFile->fixer->replaceToken($i, '');
                            }

                            $phpcsFile->fixer->replaceToken(($index['value'] - 1), str_repeat(' ', $expected));
                            $phpcsFile->fixer->endChangeset();
                        } elseif (0 === $found) {
                            $phpcsFile->fixer->addContent(($index['value'] - 1), str_repeat(' ', $expected));
                        } else {
                            $phpcsFile->fixer->replaceToken(($index['value'] - 1), str_repeat(' ', $expected));
                        }
                    }
                }
            }

            // Check each line ends in a comma.
            $valueLine = $tokens[$index['value']]['line'];
            $nextComma = false;
            for ($i = $index['value']; $i < $arrayEnd; $i++) {
                // Skip bracketed statements, like function calls.
                if (T_OPEN_PARENTHESIS === $tokens[$i]['code']) {
                    $i         = $tokens[$i]['parenthesis_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if (T_ARRAY === $tokens[$i]['code']) {
                    $i         = $tokens[$tokens[$i]['parenthesis_opener']]['parenthesis_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                // Skip to the end of multi-line strings.
                if (isset(Tokens::$stringTokens[$tokens[$i]['code']]) === true) {
                    $i = $phpcsFile->findNext($tokens[$i]['code'], ($i + 1), null, true);
                    $i--;
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if (T_OPEN_SHORT_ARRAY === $tokens[$i]['code']) {
                    $i         = $tokens[$i]['bracket_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if (T_CLOSURE === $tokens[$i]['code']) {
                    $i         = $tokens[$i]['scope_closer'];
                    $valueLine = $tokens[$i]['line'];
                    continue;
                }

                if (T_COMMA === $tokens[$i]['code']) {
                    $nextComma = $i;
                    break;
                }
            }

            if (false === $nextComma || ($tokens[$nextComma]['line'] !== $valueLine)) {
                $error = 'Each line in an array declaration must end in a comma';
                $fix   = $phpcsFile->addFixableError($error, $index['value'], 'NoComma');

                if (true === $fix) {
                    // Find the end of the line and put a comma there.
                    for ($i = ($index['value'] + 1); $i < $arrayEnd; $i++) {
                        if ($tokens[$i]['line'] > $valueLine) {
                            break;
                        }
                    }

                    $phpcsFile->fixer->addContentBefore(($i - 1), ',');
                }
            }

            // Check that there is no space before the comma.
            if (false !== $nextComma && T_WHITESPACE === $tokens[($nextComma - 1)]['code']) {
                $content     = $tokens[($nextComma - 2)]['content'];
                $spaceLength = $tokens[($nextComma - 1)]['length'];
                $error       = 'Expected 0 spaces between "%s" and comma; %s found';
                $data        = array($content, $spaceLength);

                $fix = $phpcsFile->addFixableError($error, $nextComma, 'SpaceBeforeComma', $data);
                if (true === $fix) {
                    $phpcsFile->fixer->replaceToken(($nextComma - 1), '');
                }
            }
        }
    }
}
