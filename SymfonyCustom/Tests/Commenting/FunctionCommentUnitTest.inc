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
    function __construct() // do not ignore
    {
    }

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
     *
     * @return void
     */
    function functionTestWithCallBack($tab)
    {
        $thing = array_map(function ($t) {
            return $t[0];
        }, $tab);
    }
}

/**
 * {@inheritdoc}
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
 * @param int $test Test argument
 */
function testToNotIgnore2($test)
{
    return 42;
}

function toNotIgnore()
{
    $test = 42;
}

/**
 * @return void
 *
 * @throws Exception
 */
function throwTest()
{
    throw new Exception();
}

/**
 * @return void
 *
 * @throws Exception|RuntimeException
 */
function throwTest2()
{
    throw new Exception();
}

/**
 * @return void
 *
 * @throws Exception
 * @throws RuntimeException
 */
function throwTest3()
{
    throw new Exception();
}
