<?php

namespace MyProject;

use BarClass as Bar;
use Foo;
use Route;
use ORM;
use Truc;
use Machin;
use Unused;

class Container
{
    /**
     * @Foo
     * @Route("/{id}")
     * @ORM\Column(type="integer")
     *
     * @param Toto
     * @var Truc
     * @return Machin
     */
    function test (Bar $bar)
    {
        return;
    }
}
