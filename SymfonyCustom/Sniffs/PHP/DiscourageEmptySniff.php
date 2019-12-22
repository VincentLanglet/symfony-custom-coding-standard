<?php

namespace SymfonyCustom\Sniffs\PHP;

use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff;

/**
 * Class DiscourageEmptySniff
 */
class DiscourageEmptySniff extends ForbiddenFunctionsSniff
{
    /**
     * @var array
     */
    public $forbiddenFunctions = ['empty' => null];

    /**
     * @var bool
     */
    public $error = false;
}
