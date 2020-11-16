<?php

declare(strict_types=1);

namespace SymfonyCustom\Sniffs\PHP;

use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff;

/**
 * Class EncourageMultiBytesSniff
 */
class EncourageMultiBytesSniff extends ForbiddenFunctionsSniff
{
    /**
     * @var array
     */
    public $forbiddenFunctions = [
        'str_split'    => 'mb_str_split',
        'stripos'      => 'mb_stripos',
        'stristr'      => 'mb_stristr',
        'strlen'       => 'mb_strlen',
        'strpos'       => 'mb_strpos',
        'strrchr'      => 'mb_strrchr',
        'strripos'     => 'mb_strripos',
        'strrpos'      => 'mb_strrpos',
        'strstr'       => 'mb_strstr',
        'strtolower'   => 'mb_strtolower',
        'strtoupper'   => 'mb_strtoupper',
        'substr_count' => 'mb_substr_count',
        'substr'       => 'mb_substr',
    ];

    /**
     * @var bool
     */
    public $error = false;
}
