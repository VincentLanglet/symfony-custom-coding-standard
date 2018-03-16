<?php

/**
 * @param string $test Test Argument
 */
function functionTest1($test)
{
    return 42;
}
/**
 * @return int
 */
function functionTest2($test)
{
    return 42;
}

/**
 * @param string $test Test Argument
 *
 * @return int
 */
function functionTest3($test)
{
    return 42;
}


/**
 * @param array $tab Test Argument
 */
function functionTestWithCallBack($tab)
{
    $thing = array_map(function ($t) {
        return $t[0];
    }, $tab);
}

class classToTestIndentation
{
    /**
     * @param string $test Test Argument
     */
    function functionTest1($test)
    {
        return 42;
    }
    /**
     * @return int
     */
    function functionTest2($test)
    {
        return 42;
    }


    /**
     * @param array $tab Test Argument
     */
    function functionTestWithCallBack($tab)
    {
        $thing = array_map(function ($t) {
            return $t[0];
        }, $tab);
    }
}

/**
 * #{@inheritdoc}
 */
function functionTestInherit($test)
{
    return 42;
}

function testToNotIgnore1($test)
{
    return 42;
}

/**
 * @param string $test should not be ignore
 */
function testToNotIgnore2($test)
{
    return 42;
}

function testToIgnore1()
{
    $test = 42;
}
function testToIgnore2()
{
    $test = 42;
}
