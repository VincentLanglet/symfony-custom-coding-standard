<?php

namespace TwigCS\Sniff;

use Twig\Environment;
use Twig\Node\Node;

/**
 * Interface PostParserSniffInterface
 */
interface PostParserSniffInterface extends SniffInterface
{
    /**
     * @param Node        $node
     * @param Environment $env
     */
    public function process(Node $node, Environment $env);
}
