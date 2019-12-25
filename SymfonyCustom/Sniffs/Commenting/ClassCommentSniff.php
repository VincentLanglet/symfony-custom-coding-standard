<?php

declare(strict_types=1);

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
        ],
        'package'    => [
            'required'       => false,
            'allow_multiple' => false,
        ],
        'subpackage' => [
            'required'       => false,
            'allow_multiple' => false,
        ],
        'author'     => [
            'required'       => false,
            'allow_multiple' => true,
        ],
        'copyright'  => [
            'required'       => false,
            'allow_multiple' => true,
        ],
        'license'    => [
            'required'       => false,
            'allow_multiple' => false,
        ],
        'version'    => [
            'required'       => false,
            'allow_multiple' => false,
        ],
        'link'       => [
            'required'       => false,
            'allow_multiple' => true,
        ],
        'see'        => [
            'required'       => false,
            'allow_multiple' => true,
        ],
        'since'      => [
            'required'       => false,
            'allow_multiple' => false,
        ],
        'deprecated' => [
            'required'       => false,
            'allow_multiple' => false,
        ],
    ];
}
