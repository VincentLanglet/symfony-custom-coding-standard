<?php

namespace MyProject;

use BarClass as Bar;
use My\Full\Classname as Another;
use SomethingElse;
use Something;

$var = new MyClass(
    function () use ($foo, $bar) {
        return true;
    }
);

class Container extends Component implements IContainer
{
    use TContainer;
    use AContainer;
}

use LastThing;

trait HelloWorld
{
    use Hello, World, The;
}
