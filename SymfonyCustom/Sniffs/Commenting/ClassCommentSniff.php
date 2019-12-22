<?php

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\ClassCommentSniff as PEARClassCommentSniff;

/**
 * Parses and verifies the doc comments for classes.
 *
 * Verifies that :
 *  - A doc comment exists.
 *  - Check the order of the tags.
 *  - Check the indentation of each tag.
 *  - Check required and optional tags and the format of their content.
 */
class ClassCommentSniff extends PEARClassCommentSniff
{
    /**
     * Tags in correct order and related info.
     *
     * @var array
     */
    protected $tags = [
        'category'   => [
            'required'       => false,
            'allow_multiple' => false,
            'order_text'     => 'precedes @package',
        ],
        'package'    => [
            'required'       => false,
            'allow_multiple' => false,
            'order_text'     => 'follows @category',
        ],
        'subpackage' => [
            'required'       => false,
            'allow_multiple' => false,
            'order_text'     => 'follows @package',
        ],
        'author'     => [
            'required'       => false,
            'allow_multiple' => true,
            'order_text'     => 'follows @subpackage (if used) or @package',
        ],
        'copyright'  => [
            'required'       => false,
            'allow_multiple' => true,
            'order_text'     => 'follows @author',
        ],
        'license'    => [
            'required'       => false,
            'allow_multiple' => false,
            'order_text'     => 'follows @copyright (if used) or @author',
        ],
        'version'    => [
            'required'       => false,
            'allow_multiple' => false,
            'order_text'     => 'follows @license',
        ],
        'link'       => [
            'required'       => false,
            'allow_multiple' => true,
            'order_text'     => 'follows @version',
        ],
        'see'        => [
            'required'       => false,
            'allow_multiple' => true,
            'order_text'     => 'follows @link',
        ],
        'since'      => [
            'required'       => false,
            'allow_multiple' => false,
            'order_text'     => 'follows @see (if used) or @link',
        ],
        'deprecated' => [
            'required'       => false,
            'allow_multiple' => false,
            'order_text'     => 'follows @since (if used) or @see (if used) or @link',
        ],
    ];
}
