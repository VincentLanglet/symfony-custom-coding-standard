<?php

namespace TwigCS\Token;

use \Exception;
use Twig\Environment;
use Twig\Lexer;
use Twig\Source;

/**
 * An override of Twig's Lexer to add whitespace and new line detection.
 */
class Tokenizer
{
    const STATE_DATA          = Lexer::STATE_DATA;
    const STATE_BLOCK         = Lexer::STATE_BLOCK;
    const STATE_VAR           = Lexer::STATE_VAR;
    const STATE_STRING        = Lexer::STATE_STRING;
    const STATE_INTERPOLATION = Lexer::STATE_INTERPOLATION;
    const STATE_COMMENT       = 5;

    const REGEX_NAME            = Lexer::REGEX_NAME;
    const REGEX_NUMBER          = Lexer::REGEX_NUMBER;
    const REGEX_STRING          = Lexer::REGEX_STRING;
    const REGEX_DQ_STRING_DELIM = Lexer::REGEX_DQ_STRING_DELIM;
    const REGEX_DQ_STRING_PART  = Lexer::REGEX_DQ_STRING_PART;
    const PUNCTUATION           = Lexer::PUNCTUATION;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string[]
     */
    private $regexes;

    /**
     * @var int
     */
    protected $cursor;

    /**
     * @var int
     */
    protected $end;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var int
     */
    protected $currentPosition;

    /**
     * @var Token[]
     */
    protected $tokens;

    /**
     * @var array
     */
    protected $tokenPositions;

    /**
     * @var int[]
     */
    protected $state;

    /**
     * @var array
     */
    protected $brackets;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @param Environment $env
     * @param array       $options
     */
    public function __construct(Environment $env, array $options = [])
    {
        $this->options = array_merge([
            'tag_comment'     => ['{#', '#}'],
            'tag_block'       => ['{%', '%}'],
            'tag_variable'    => ['{{', '}}'],
            'whitespace_trim' => '-',
            'interpolation'   => ['#{', '}'],
        ], $options);

        $tokenizerHelper = new TokenizerHelper($env, $this->options);
        $this->regexes = [
            'lex_block'        => $tokenizerHelper->getBlockRegex(),
            'lex_comment'      => $tokenizerHelper->getCommentRegex(),
            'lex_variable'     => $tokenizerHelper->getVariableRegex(),
            'operator'         => $tokenizerHelper->getOperatorRegex(),
            'lex_tokens_start' => $tokenizerHelper->getTokensStartRegex(),
        ];
    }

    /**
     * @param Source $source
     *
     * @return Token[]
     *
     * @throws Exception
     */
    public function tokenize(Source $source)
    {
        $this->resetState($source);
        $this->preflightSource($this->code);

        while ($this->cursor < $this->end) {
            $lastToken = $this->getTokenPosition();
            $nextToken = $this->getTokenPosition(1);

            while (null !== $nextToken && $nextToken['position'] < $this->cursor) {
                $this->moveCurrentPosition();
                $lastToken = $nextToken;
                $nextToken = $this->getTokenPosition(1);
            }

            switch ($this->getState()) {
                case self::STATE_BLOCK:
                    $this->lexBlock();
                    break;
                case self::STATE_VAR:
                    $this->lexVariable();
                    break;
                case self::STATE_COMMENT:
                    $this->lexComment();
                    break;
                case self::STATE_DATA:
                    if (null !== $lastToken && $this->cursor === $lastToken['position']) {
                        $this->lexStart();
                    } else {
                        $this->lexData();
                    }
                    break;
                default:
                    throw new Exception('Unhandled state in tokenize', 1);
            }
        }

        if (self::STATE_DATA !== $this->getState()) {
            throw new Exception('Error Processing Request', 1);
        }

        $this->pushToken(Token::EOF_TYPE);

        return $this->tokens;
    }

    /**
     * @param Source $source
     */
    protected function resetState(Source $source)
    {
        $this->cursor = 0;
        $this->line = 1;
        $this->currentPosition = 0;
        $this->tokens = [];
        $this->state = [];

        $this->code = str_replace(["\r\n", "\r"], "\n", $source->getCode());
        $this->end = strlen($this->code);
        $this->filename = $source->getName();
    }

    /**
     * @return int
     */
    protected function getState()
    {
        return !empty($this->state) ? $this->state[count($this->state) - 1] : self::STATE_DATA;
    }

    /**
     * @param int $state
     */
    protected function pushState($state)
    {
        $this->state[] = $state;
    }

    /**
     * @throws Exception
     */
    protected function popState()
    {
        if (0 === count($this->state)) {
            throw new Exception('Cannot pop state without a previous state');
        }
        array_pop($this->state);
    }

    /**
     * @param string $code
     */
    protected function preflightSource($code)
    {
        $tokenPositions = [];
        preg_match_all($this->regexes['lex_tokens_start'], $code, $tokenPositions, PREG_OFFSET_CAPTURE);

        $tokenPositionsReworked = [];
        foreach ($tokenPositions[0] as $index => $tokenFullMatch) {
            $tokenPositionsReworked[$index] = [
                'fullMatch' => $tokenFullMatch[0],
                'position'  => $tokenFullMatch[1],
                'match'     => $tokenPositions[1][$index][0],
            ];
        }

        $this->tokenPositions = $tokenPositionsReworked;
    }

    /**
     * @param int $offset
     *
     * @return array|null
     */
    protected function getTokenPosition($offset = 0)
    {
        if (empty($this->tokenPositions)
            || !isset($this->tokenPositions[$this->currentPosition + $offset])
        ) {
            return null;
        }

        return $this->tokenPositions[$this->currentPosition + $offset];
    }

    /**
     * @param int $value
     */
    protected function moveCurrentPosition($value = 1)
    {
        $this->currentPosition += $value;
    }

    /**
     * @param string $value
     */
    protected function moveCursor($value)
    {
        $this->cursor += strlen($value);
        $this->line += substr_count($value, "\n");
    }

    /**
     * @param int         $type
     * @param string|null $value
     */
    protected function pushToken($type, $value = null)
    {
        $tokenPositionInLine = $this->cursor - strrpos(substr($this->code, 0, $this->cursor), PHP_EOL);
        $this->tokens[] = new Token($type, $this->line, $tokenPositionInLine, $this->filename, $value);
    }

    /**
     * @param int    $endType
     * @param string $endRegex
     *
     * @throws Exception
     */
    protected function lex($endType, $endRegex)
    {
        preg_match($endRegex, $this->code, $match, PREG_OFFSET_CAPTURE, $this->cursor);

        if (!isset($match[0])) {
            $this->lexExpression();
        } elseif ($match[0][1] === $this->cursor) {
            $this->pushToken($endType, $match[0][0]);
            $this->moveCursor($match[0][0]);
            $this->moveCurrentPosition();
            $this->popState();
        } elseif ($this->getState() === self::STATE_COMMENT) {
            // Parse as text until the end position.
            $this->lexData($match[0][1]);
        } else {
            while ($this->cursor < $match[0][1]) {
                $this->lexExpression();
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function lexExpression()
    {
        $currentToken = $this->code[$this->cursor];
        if (preg_match('/\t/', $currentToken)) {
            $this->lexTab();
        } elseif (' ' === $currentToken) {
            $this->lexWhitespace();
        } elseif (PHP_EOL === $currentToken) {
            $this->lexEOL();
        } elseif (preg_match($this->regexes['operator'], $this->code, $match, null, $this->cursor)) {
            // operators
            $this->pushToken(Token::OPERATOR_TYPE, $match[0]);
            $this->moveCursor($match[0]);
        } elseif (preg_match(self::REGEX_NAME, $this->code, $match, null, $this->cursor)) {
            // names
            $this->pushToken(Token::NAME_TYPE, $match[0]);
            $this->moveCursor($match[0]);
        } elseif (preg_match(self::REGEX_NUMBER, $this->code, $match, null, $this->cursor)) {
            // numbers
            $number = (float) $match[0];  // floats
            if (ctype_digit($match[0]) && $number <= PHP_INT_MAX) {
                $number = (int) $match[0]; // integers lower than the maximum
            }
            $this->pushToken(Token::NUMBER_TYPE, $number);
            $this->moveCursor($match[0]);
        } elseif (false !== strpos(self::PUNCTUATION, $this->code[$this->cursor])) {
            // punctuation
            if (false !== strpos('([{', $this->code[$this->cursor])) {
                // opening bracket
                $this->brackets[] = [$this->code[$this->cursor], $this->line];
            } elseif (false !== strpos(')]}', $this->code[$this->cursor])) {
                // closing bracket
                if (empty($this->brackets)) {
                    throw new Exception(sprintf('Unexpected "%s"', $this->code[$this->cursor]));
                }
                $expect = array_pop($this->brackets)[0];
                if (strtr($expect, '([{', ')]}') !== $this->code[$this->cursor]) {
                    throw new Exception(sprintf('Unclosed "%s"', $expect));
                }
            }
            $this->pushToken(Token::PUNCTUATION_TYPE, $this->code[$this->cursor]);
            $this->moveCursor($this->code[$this->cursor]);
        } elseif (preg_match(self::REGEX_STRING, $this->code, $match, null, $this->cursor)) {
            // strings
            $this->pushToken(Token::STRING_TYPE, stripcslashes($match[0]));
            $this->moveCursor($match[0]);
        } else {
            // unlexable
            throw new Exception(sprintf('Unexpected character "%s"', $currentToken));
        }
    }

    /**
     * @throws Exception
     */
    protected function lexBlock()
    {
        $this->lex(Token::BLOCK_END_TYPE, $this->regexes['lex_block']);
    }

    /**
     * @throws Exception
     */
    protected function lexVariable()
    {
        $this->lex(Token::VAR_END_TYPE, $this->regexes['lex_variable']);
    }

    /**
     * @throws Exception
     */
    protected function lexComment()
    {
        $this->lex(Token::COMMENT_END_TYPE, $this->regexes['lex_comment']);
    }

    /**
     * @param int $limit
     */
    protected function lexData($limit = 0)
    {
        $nextToken = $this->getTokenPosition();
        if (0 === $limit && null !== $nextToken) {
            $limit = $nextToken['position'];
        }

        $currentToken = $this->code[$this->cursor];
        if (preg_match('/\t/', $currentToken)) {
            $this->lexTab();
        } elseif (' ' === $currentToken) {
            $this->lexWhitespace();
        } elseif (PHP_EOL === $currentToken) {
            $this->lexEOL();
        } elseif (preg_match('/\S+/', $this->code, $match, null, $this->cursor)) {
            $value = $match[0];
            // Stop if cursor reaches the next token start.
            if (0 !== $limit && $limit <= ($this->cursor + strlen($value))) {
                $value = substr($value, 0, $limit - $this->cursor);
            }
            // Fixing token start among expressions and comments.
            $nbTokenStart = preg_match_all($this->regexes['lex_tokens_start'], $value, $matches);
            if ($nbTokenStart) {
                $this->moveCurrentPosition($nbTokenStart);
            }
            $this->pushToken(Token::TEXT_TYPE, $value);
            $this->moveCursor($value);
        }
    }

    /**
     * @throws Exception
     */
    protected function lexStart()
    {
        $tokenStart = $this->getTokenPosition();
        if ($tokenStart['match'] === $this->options['tag_comment'][0]) {
            $state = self::STATE_COMMENT;
            $tokenType = Token::COMMENT_START_TYPE;
        } elseif ($tokenStart['match'] === $this->options['tag_block'][0]) {
            $state = self::STATE_BLOCK;
            $tokenType = Token::BLOCK_START_TYPE;
        } elseif ($tokenStart['match'] === $this->options['tag_variable'][0]) {
            $state = self::STATE_VAR;
            $tokenType = Token::VAR_START_TYPE;
        } else {
            throw new Exception(sprintf('Unhandled tag "%s" in lexStart', $tokenStart['match']), 1);
        }

        $this->pushToken($tokenType, $tokenStart['fullMatch']);
        $this->pushState($state);
        $this->moveCursor($tokenStart['fullMatch']);
    }

    protected function lexTab()
    {
        $this->pushToken(Token::TAB_TYPE);
        $this->moveCursor($this->code[$this->cursor]);
    }

    protected function lexWhitespace()
    {
        $this->pushToken(Token::WHITESPACE_TYPE, $this->code[$this->cursor]);
        $this->moveCursor($this->code[$this->cursor]);
    }

    protected function lexEOL()
    {
        $this->pushToken(Token::EOL_TYPE, $this->code[$this->cursor]);
        $this->moveCursor($this->code[$this->cursor]);
    }
}
