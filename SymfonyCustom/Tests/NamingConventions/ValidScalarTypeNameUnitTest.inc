<?php

echo (bool) $a;
echo (int) $a;
echo (float) $a;
echo ( float ) $a;

/**
 * @param bool  $a
 * @param int   $b
 * @param float $c
 *
 * @return bool
 * @return int
 * @return float
 */

/** @var bool $a */
/** @var int $b */
/** @var float $c */

// Only PHPDoc comments are checked
/* @var boolean $a */


echo ( integer ) $a;
echo (boolean) $a;
echo (Boolean) $a;
echo (integer) $a;
echo (double) $a;
echo (real) $a;

/**
 * @param boolean $a
 * @param Boolean $a
 * @param integer $b
 * @param double  $c
 * @param real    $c
 *
 * @return boolean
 * @return Boolean
 * @return integer
 * @return double
 * @return real
 */

/** @var boolean $a */
/** @var Boolean $a */
/** @var integer $b */
/** @var double $c */
/** @var real $c */
