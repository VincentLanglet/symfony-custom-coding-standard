<?php

/**
 * @param string $test Test Argument
 */
function functionTest1($test)
{
    return 42;
}

/**
 * @param string $test Test Argument
 *
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
