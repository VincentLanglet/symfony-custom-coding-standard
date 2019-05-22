<?php

namespace TwigCS\Extension;

use Twig\Environment;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;
use Twig\NodeVisitor\NodeVisitorInterface;
use TwigCS\Sniff\PostParserSniffInterface;

/**
 * Node visitors provide a mechanism for manipulating nodes before a template is
 * compiled down to a PHP class.
 *
 * This class is using that mechanism to execute sniffs (rules) on all the twig
 * node during a template compilation; thanks to `Twig_Parser`.
 */
class SniffsNodeVisitor extends AbstractNodeVisitor implements NodeVisitorInterface
{
    /**
     * List of sniffs to be executed.
     *
     * @var array
     */
    protected $sniffs;

    /**
     * Is this node visitor enabled?
     *
     * @var bool
     */
    protected $enabled;

    public function __construct()
    {
        $this->sniffs = [];
        $this->enabled = true;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * Register a sniff to be executed.
     *
     * @param PostParserSniffInterface $sniff
     */
    public function addSniff(PostParserSniffInterface $sniff)
    {
        $this->sniffs[] = $sniff;
    }

    /**
     * Remove a sniff from the node visitor.
     *
     * @param PostParserSniffInterface $toBeRemovedSniff
     *
     * @return self
     */
    public function removeSniff(PostParserSniffInterface $toBeRemovedSniff)
    {
        foreach ($this->sniffs as $index => $sniff) {
            if ($toBeRemovedSniff === $sniff) {
                unset($this->sniffs[$index]);
            }
        }

        return $this;
    }

    /**
     * Get all registered sniffs.
     *
     * @return array
     */
    public function getSniffs()
    {
        return $this->sniffs;
    }

    /**
     * Enable this node visitor.
     *
     * @return self
     */
    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * Disable this node visitor.
     *
     * @return self
     */
    public function disable()
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * @param Node        $node
     * @param Environment $env
     *
     * @return Node
     */
    protected function doEnterNode(Node $node, Environment $env)
    {
        if (!$this->enabled) {
            return $node;
        }

        foreach ($this->getSniffs() as $sniff) {
            $sniff->process($node, $env);
        }

        return $node;
    }

    /**
     * @param Node        $node
     * @param Environment $env
     *
     * @return Node
     */
    protected function doLeaveNode(Node $node, Environment $env)
    {
        return $node;
    }
}
