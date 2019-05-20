<?php

namespace TwigCS\Token;

use Twig\Source;

/**
 * Interface TokenizerInterface
 */
interface TokenizerInterface
{
    /**
     * @param Source $code
     *
     * @return Token[]
     */
    public function tokenize(Source $code);
}
