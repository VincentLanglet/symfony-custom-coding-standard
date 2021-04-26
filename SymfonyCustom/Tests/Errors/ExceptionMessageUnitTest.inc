<?php

class Errors
{
    /**
     * @param bool   $bool
     * @param string $option
     *
     * @throws \RuntimeException
     */
    public function test($bool, $option)
    {
        if ($bool) {
            throw new \RuntimeException(sprintf('Unrecognized option "%s"', $option));
        }

        throw new \RuntimeException('Unrecognized option '.$option);
    }

    public function testLast(\Exception $exception)
    {
        throw $exception;
    }
}
