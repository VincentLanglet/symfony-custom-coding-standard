<?php

namespace TwigCS\Sniff;

use \Exception;
use Twig\Node\Expression\Binary\ConcatBinary;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\GetAttrExpression;
use Twig\Node\IncludeNode;
use Twig\Node\Node;
use TwigCS\Report\SniffViolation;

/**
 * Base for all post-parser sniff.
 *
 * A post parser sniff should be useful to check actual values of twig functions, filters
 * and tags such as: ensure that a given function has at least 3 arguments or if the template
 * contains an {% include %} tag.
 *
 * Use `AbstractPreParserSniff` sniff if you want to check syntax and code formatting.
 */
abstract class AbstractPostParserSniff extends AbstractSniff implements PostParserSniffInterface
{
    /**
     * @return string
     */
    public function getType()
    {
        return $this::TYPE_POST_PARSER;
    }

    /**
     * Adds a violation to the current report for the given node.
     *
     * @param int    $messageType
     * @param string $message
     * @param Node   $node
     *
     * @return self
     *
     * @throws Exception
     */
    public function addMessage($messageType, $message, Node $node)
    {
        $sniffViolation = new SniffViolation(
            $messageType,
            $message,
            $this->getTemplateLine($node),
            $this->getTemplateName($node)
        );

        $this->getReport()->addMessage($sniffViolation);

        return $this;
    }

    /**
     * @param Node $node
     *
     * @return int|null
     */
    public function getTemplateLine(Node $node)
    {
        if (method_exists($node, 'getTemplateLine')) {
            return $node->getTemplateLine();
        }

        if (method_exists($node, 'getLine')) {
            return $node->getLine();
        }

        return null;
    }

    /**
     * @param Node $node
     *
     * @return string
     */
    public function getTemplateName(Node $node)
    {
        if (method_exists($node, 'getTemplateName')) {
            return $node->getTemplateName();
        }

        if (method_exists($node, 'getFilename')) {
            return $node->getFilename();
        }

        if ($node->hasAttribute('filename')) {
            return $node->getAttribute('filename');
        }

        return '';
    }

    /**
     * @param Node        $node
     * @param string      $type
     * @param string|null $name
     *
     * @return bool
     */
    public function isNodeMatching(Node $node, $type, $name = null)
    {
        $typeToClass = [
            'filter'   => function (Node $node, $type, $name) {
                return $node instanceof FilterExpression
                    && $name === $node->getNode($type)->getAttribute('value');
            },
            'function' => function (Node $node, $type, $name) {
                return $node instanceof FunctionExpression
                    && $name === $node->getAttribute('name');
            },
            'include'  => function (Node $node, $type, $name) {
                return $node instanceof IncludeNode;
            },
            'tag'      => function (Node $node, $type, $name) {
                return $node->getNodeTag() === $name;
            },
        ];

        if (!isset($typeToClass[$type])) {
            return false;
        }

        return $typeToClass[$type]($node, $type, $name);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function stringifyValue($value)
    {
        if (null === $value) {
            return 'null';
        }

        if (is_bool($value)) {
            return ($value) ? 'true' : 'false';
        }

        return (string) $value;
    }

    /**
     * @param Node $node
     *
     * @return mixed|string
     */
    public function stringifyNode(Node $node)
    {
        $stringValue = '';

        if ($node instanceof GetAttrExpression) {
            return $node->getNode('node')->getAttribute('name').'.'.$this->stringifyNode($node->getNode('attribute'));
        }
        if ($node instanceof ConcatBinary) {
            return $this->stringifyNode($node->getNode('left')).' ~ '.$this->stringifyNode($node->getNode('right'));
        }
        if ($node instanceof ConstantExpression) {
            return $node->getAttribute('value');
        }

        return $stringValue;
    }
}
