<?php

class Test
{
    public function tester($dummy, $mergedOptions)
    {
        if (true === $dummy) {
            return null;
        }

        if ('string' === $dummy) {
            if ('values' === $mergedOptions['some_default']) {
                return substr($dummy, 0, 5);
            }

            return ucwords($dummy);
        }

        if ('int' === $dummy) {
            if ('values' === $mergedOptions['some_default']) {
                return substr($dummy, 0, 5);
            } elseif (true) {
                return ucwords($dummy);
            }
        }

        if ('a' === $dummy) {
            $mergedOptions['a'] = true;
        } else {
            $mergedOptions['a'] = false;
        }

        if ('b' === $dummy) {
            $mergedOptions['a'] = true;

            throw new Error();
        } else {
            $mergedOptions['a'] = false;
        }

        switch (true) {
            case false:
                return null;
                break;
            default:
                break;
        }

        if ('c' === $dummy) {
            $function = function () {
                return null;
            };
        } elseif ('d' === $dummy) {
            return null;
        }

        if (1 === 1) {
            if (2 === 3) {
                return null;
            }
        } else {
            return null;
        }

        return null;
    }

    public function __construct()
    {
        if (1 === 1) {
            if (2 === 2) {
                return null;
            }
        } else {
            $a = 1;
        }

        if ($a = 1) {
            $b = 2;
        }
    }

    public function ok()
    {
        if (false) {
            try {
                $a = 1;
            } catch (NumberParseException $e) {
                $a = 1;

                return;
            }
        } else {
            $a = 1;
        }
    }
}

