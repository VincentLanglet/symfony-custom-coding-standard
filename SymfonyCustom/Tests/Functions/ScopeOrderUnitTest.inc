<?php

class myClass1
{
    public function functionPublic1() {}

    protected function functionProtected1() {}

    public function functionPublic2() {}
}

class myClass2
{
    public function functionPublic1() {}

    protected function functionProtected1() {}

    private function functionPrivate() {}

    protected function functionProtected2() {}
}

class Embedded
{
    public function test()
    {
        $class = new class
        {
            private function foo() {}

            protected function bar() {}
        };

        return new class
        {
            protected function foo() {}
        };
    }

    public function test2()
    {
        return new class
        {
            protected function foo() {}
        };
    }
}
