<?php

namespace TwigCS\Token;

use Twig\Environment;

/**
 * Class TokenizerHelper
 */
class TokenizerHelper
{
    /**
     * @var Environment
     */
    private $env;

    /**
     * @var array
     */
    private $options;

    /**
     * @param Environment $env
     * @param array       $options
     */
    public function __construct(Environment $env, array $options = [])
    {
        $this->env = $env;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getVarRegex()
    {
        return '{
            \s*
            (?:'.
            preg_quote($this->options['whitespace_trim'].$this->options['tag_variable'][1], '#').'\s*'.
            '|'.
            preg_quote($this->options['whitespace_line_trim'].$this->options['tag_variable'][1], '#').'['.$this->options['whitespace_line_chars'].']*'.
            '|'.
            preg_quote($this->options['tag_variable'][1], '#').
            ')
        }Ax';
    }

    /**
     * @return string
     */
    public function getBlockRegex()
    {
        return '{
            \s*
            (?:'.
            preg_quote($this->options['whitespace_trim'].$this->options['tag_block'][1], '#').'\s*\n?'.
            '|'.
            preg_quote($this->options['whitespace_line_trim'].$this->options['tag_block'][1], '#').'['.$this->options['whitespace_line_chars'].']*'.
            '|'.
            preg_quote($this->options['tag_block'][1], '#').'\n?'.
            ')
        }Ax';
    }

    /**
     * @return string
     */
    public function getRawDataRegex()
    {
        return '{'.
            preg_quote($this->options['tag_block'][0], '#').
            '('.
            $this->options['whitespace_trim'].
            '|'.
            $this->options['whitespace_line_trim'].
            ')?\s*endverbatim\s*'.
            '(?:'.
            preg_quote($this->options['whitespace_trim'].$this->options['tag_block'][1], '#').'\s*'.
            '|'.
            preg_quote($this->options['whitespace_line_trim'].$this->options['tag_block'][1], '#').'['.$this->options['whitespace_line_chars'].']*'.
            '|'.
            preg_quote($this->options['tag_block'][1], '#').
            ')
        }sx';
    }

    /**
     * @return string
     */
    public function getOperatorRegex()
    {
        $operators = array_merge(
            ['='],
            array_keys($this->env->getUnaryOperators()),
            array_keys($this->env->getBinaryOperators())
        );

        $operators = array_combine($operators, array_map('strlen', $operators));
        arsort($operators);

        $regex = [];
        foreach ($operators as $operator => $length) {
            if (ctype_alpha($operator[$length - 1])) {
                $r = preg_quote($operator, '/').'(?=[\s()])';
            } else {
                $r = preg_quote($operator, '/');
            }

            $r = preg_replace('/\s+/', '\s+', $r);

            $regex[] = $r;
        }

        return '/'.implode('|', $regex).'/A';
    }

    /**
     * @return string
     */
    public function getCommentRegex()
    {
        return '{
            (?:'.
            preg_quote($this->options['whitespace_trim']).preg_quote($this->options['tag_comment'][1], '#').'\s*\n?'.
            '|'.
            preg_quote($this->options['whitespace_line_trim'].$this->options['tag_comment'][1], '#').'['.$this->options['whitespace_line_chars'].']*'.
            '|'.
            preg_quote($this->options['tag_comment'][1], '#').'\n?'.
            ')
        }sx';
    }

    /**
     * @return string
     */
    public function getBlockRawRegex()
    {
        return '{
            \s*verbatim\s*
            (?:'.
            preg_quote($this->options['whitespace_trim'].$this->options['tag_block'][1], '#').'\s*'.
            '|'.
            preg_quote($this->options['whitespace_line_trim'].$this->options['tag_block'][1], '#').'['.$this->options['whitespace_line_chars'].']*'.
            '|'.
            preg_quote($this->options['tag_block'][1], '#').
            ')
        }Asx';
    }

    /**
     * @return string
     */
    public function getBlockLineRegex()
    {
        return '{\s*line\s+(\d+)\s*'.preg_quote($this->options['tag_block'][1], '#').'}As';
    }

    /**
     * @return string
     */
    public function getTokensStartRegex()
    {
        return '{
            ('.
            preg_quote($this->options['tag_variable'][0], '#').
            '|'.
            preg_quote($this->options['tag_block'][0], '#').
            '|'.
            preg_quote($this->options['tag_comment'][0], '#').
            ')('.
            preg_quote($this->options['whitespace_trim'], '#').
            '|'.
            preg_quote($this->options['whitespace_line_trim'], '#').
            ')?
        }sx';
    }

    /**
     * @return string
     */
    public function getInterpolationStartRegex()
    {
        return '{'.preg_quote($this->options['interpolation'][0], '#').'\s*}A';
    }

    /**
     * @return string
     */
    public function getInterpolationEndRegex()
    {
        return '{\s*'.preg_quote($this->options['interpolation'][1], '#').'}A';
    }
}
