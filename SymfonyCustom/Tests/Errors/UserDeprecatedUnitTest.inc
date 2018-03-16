<?php

class Errors
{
    /**
     * @return string
     *
     * @deprecated
     */
    public function someGoodDeprecatedMethod()
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return Baz::someMethod();
    }

    /**
     * @return string
     *
     * @deprecated
     */
    public function someBadDeprecatedMethod()
    {
        trigger_error(
            sprintf(
                'The %s() method is deprecated',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return Baz::someMethod();
    }
}
