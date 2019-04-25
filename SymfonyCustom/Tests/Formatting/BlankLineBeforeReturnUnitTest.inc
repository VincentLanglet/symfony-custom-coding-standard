<?php
function validFunctionReturnOne()
{
    return;
}

function validFunctionReturnTwo()
{

    return;
}

function validFunctionReturnThree()
{
    echo "";

    return;
}

function validFunctionReturnFour()
{
    // comment
    return;
}

function validFunctionReturnFive()
{
    /**
     * multi-line
     */
    return;
}

function validFunctionReturnSix()
{
    /** comment */
    return;
}

function validFunctionReturnSeven()
{
    echo "";
    /** comment */

    return;
}

function validFunctionReturnEight()
{
    echo "";
    // comment

    // comment
    return;
}

function invalidFunctionReturnOne()
{
    echo "";
    return;
}

function invalidFunctionReturnTwo()
{
    echo "";
    /** comment */
    return;
}

function invalidFunctionReturnThree()
{
    echo "";
    /**
     * comment
     */
    return;
}

switch ($condition) {
    case 'foo':
        return true;

    default:
        return false;
}
