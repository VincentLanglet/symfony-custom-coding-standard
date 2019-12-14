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
    public function getBlockRegex()
    {
        return '/'
            .'('.preg_quote($this->options['whitespace_trim']).')?'
            .'('.preg_quote($this->options['tag_block'][1]).')'
            .'/A';
    }

    /**
     * @return string
     */
    public function getCommentRegex()
    {
        return '/'
            .'('.preg_quote($this->options['whitespace_trim']).')?'
            .'('.preg_quote($this->options['tag_comment'][1]).')'
            .'/';
    }

    /**
     * @return string
     */
    public function getVariableRegex()
    {
        return '/'
            .'('.preg_quote($this->options['whitespace_trim']).')?'
            .'('.preg_quote($this->options['tag_variable'][1]).')'
            .'/A';
    }

    /**
     * @return string
     */
    public function getTokensStartRegex()
    {
        return '/'
            .'('
                .preg_quote($this->options['tag_variable'][0], '/')
                .'|'.preg_quote($this->options['tag_block'][0], '/')
                .'|'.preg_quote($this->options['tag_comment'][0], '/')
            .')'
            .'('.preg_quote($this->options['whitespace_trim'], '/').')?'
            .'/s';
    }

    /**
     * @return string
     */
    public function getOperatorRegex()
    {
        $operators = array_merge(
            ['=', '?'],
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
}
