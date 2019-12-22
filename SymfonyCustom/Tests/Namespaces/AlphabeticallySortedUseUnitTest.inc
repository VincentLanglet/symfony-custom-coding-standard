<?php

echo namespace\func();

namespace Foo {
    use const D;
    use const C;
    use B;
    use A;
    use function Y;
    use function X;

    $foo = 1;
    $closure = function ($x) use ($foo) {
    };
}

namespace Bar {
    use A;
    use function B;
    use const C;
    use D;
    use function E;
    use const F;

    class J {
        use MyTrait;
    }
}

namespace C {
    use ArrayObject;

    use DateTime;
    use function array_keys;



    use const PHP_EOL; use const PHP_VERSION_ID;
}

namespace D {
    use Psr\NamespaceSeparator;
    use PSR7Number;

    function A() {
        return;
    }

    use PSR1Number;
    use PsrLetter;
    use Psr_Underscore;
}

namespace Foo;

use const D;
use const C;
use B;
use A;
use function Y;
use function X; /** Comment */
use A\TagManager;
use A\Tag;
use A\Tag\Tags;
use C\Response as MyResponse;
use C\Response\HtmlResponse;

class J {
    use MyTrait;
    use AnotherTrait;

    public function closure($foo)
    {
        return function ($x) use ($foo) {
            return $x <=> $foo;
        };
    }

    public function anonym()
    {
        return new class() {
            use AndAnotherTrait;
        };
    }
}

namespace FooBis;

use D;
use PHP_CodeSniffer\Files\{File, FileList};
use PHP_CodeSniffer\Files\LocalFile;
