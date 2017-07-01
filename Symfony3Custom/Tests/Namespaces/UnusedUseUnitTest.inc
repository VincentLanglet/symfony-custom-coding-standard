<?php

namespace MyProject;

use BarClass as Bar;
use Foo;
use Route;
use ORM;
use Truc;
use Machin;
use Machine;
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
     * @return Machin|Machine
     */
    function test (Bar $bar)
    {
        return;
    }
}
