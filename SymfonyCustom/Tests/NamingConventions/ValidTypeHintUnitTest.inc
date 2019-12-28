<?php

// Only PHPDoc comments are checked
/* @var boolean $a */

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

/** @method integer|string  */
/** @method array<integer,boolean>|integer[]|array<integer> truc */
/** @method array<integer, boolean|null>|integer[]|array<array<integer>> truc */

/**
 * @return null|int
 * @return string[]|null|int[]
 * @return Foo<integer|null|string, boolean|null|string>|null|int[]
 */

/** @method array < integer , null | boolean > | integer [ ] | array < array < integer > > truc */
/** @method Generator<integer, string, string, boolean> truc */
/** @method Generator<array<integer>, array<integer>, array<integer>, array<integer>> truc */
/** @method array { integer: integer, boolean?: boolean } truc */
/** @param class-string|class-string<Client>|integer truc */

/**
 * @param array{0: integer, 1?: integer}
 * @param array{integer, integer}
 * @param array{foo: integer, bar: string}
 */

/**
 * @param (integer|string)|bool
 * @param (integer&string)|bool
 * @param integer&string|bool
 */
