<?php

namespace TwigCS\Token;

use Exception;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;
use Twig\TokenStream;

/**
 * Token parser for any block.
 */
class TokenParser extends AbstractTokenParser
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    public function decideEnd(Token $token)
    {
        return $token->test('end'.$this->name);
    }

    /**
     * @param Token $token
     *
     * @return Node
     *
     * @throws Exception
     */
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();

        while ($stream->getCurrent()->getType() !== Token::BLOCK_END_TYPE) {
            $stream->next();
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        if ($this->hasBody($stream)) {
            $this->parser->subparse([$this, 'decideEnd'], true);
            $stream->expect(Token::BLOCK_END_TYPE);
        }

        $attributes = [];
        if ($token->getValue()) {
            $attributes['name'] = $token->getValue();
        }

        return new Node([], $attributes, $token->getLine(), $token->getValue() ?: null);
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->name;
    }

    /**
     * @param TokenStream $stream
     *
     * @return bool
     *
     * @throws Exception
     */
    private function hasBody(TokenStream $stream)
    {
        $look = 0;
        while ($token = $stream->look($look)) {
            if ($token->getType() === Token::EOF_TYPE) {
                return false;
            }

            if ($token->getType() === Token::NAME_TYPE
                && $token->getValue() === 'end'.$this->name
            ) {
                return true;
            }

            $look++;
        }

        return false;
    }
}
