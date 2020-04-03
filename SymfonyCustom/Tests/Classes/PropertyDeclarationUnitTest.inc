<?php

class ClassTest
{
    public function functionTest() {

    }

    private $property;
}

class Bar
{
    public function doStuff()
    {
        $var1 = 'foo';
        $var2 = 'bar';

        $anonClass = new class($var1, $var2)
        {
            private $property;

            public function thisIsAfterTheProperty()
            {
            }
        };
    }
}

class MoreBar
{
    public function doStuff()
    {
        $var1 = 'foo';
        $var2 = 'bar';

        $anonClass = new class($var1, $var2)
        {
            public function thisShouldBeAfterTheProperty()
            {
            }

            private $property;
        };
    }
}
