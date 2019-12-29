<?php

// Only PHPDoc comments are checked
/* @var boolean $a */
/** @var boolean $a */

/**
 * Check simple type
 *
 * @param boolean $a
 * @param integer $b
 * @param double $c
 * @param real $c
 */

/**
 * Don't care about case
 *
 * @return BoOlEAn $a
 * @return boolean $a
 */

/**
 * Works with different tags
 *
 * @method integer someFunction()
 * @property integer $integer
 * @property-read integer $integer
 * @property-write integer $integer
 * @throws integer
 */

/**
 * Union et intersection types
 *
 * @param integer|(string|boolean)
 * @param integer&string|boolean This syntax is not valid
 * @param (integer&string)|boolean
 * @param (integer|((string|boolean)))
 */

/**
 * Array, generic and object types
 *
 * @param array<integer,boolean>|integer[]|array<integer> foo
 * @param Generator<integer, string, string, boolean> bar
 * @param Generator<array<integer>, array<integer>, array<integer>, array<integer>> baz
 * @param array<integer[][][]>[]|boolean
 * @param array{0: integer, 1?: integer}
 * @param array{integer, integer}
 * @param array{foo: integer, bar: string}
 */

/**
 * Class-string
 *
 * @param class-string|class-string<Client>|integer
 */

/**
 * Handle space (Last one is a comment, correctly not replaced)
 *
 * @param array < integer , integer | boolean > | integer [ ] | array < array < integer > > integer
 * @param array { integer: integer, boolean?: boolean } integer
 */

/**
 * null type should be the last one
 *
 * @param null|int
 * @param string[]|null|int[]
 * @param Foo<integer|null|string, boolean|null|string>|null|int[]
 */
