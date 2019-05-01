<?php

namespace SymfonyCustom\Sniffs\Commenting;

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\ClassCommentSniff as PEARClassCommentSniff;

/**
 * Parses and verifies the doc comments for classes.
 *
 * Verifies that :
 * <ul>
 *  <li>A doc comment exists.</li>
 *  <li>There is a blank newline after the short description.</li>
 *  <li>There is a blank newline between the long and short description.</li>
 *  <li>There is a blank newline between the long description and tags.</li>
 *  <li>Check the order of the tags.</li>
 *  <li>Check the indentation of each tag.</li>
 *  <li>Check required and optional tags and the format of their content.</li>
 * </ul>
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
