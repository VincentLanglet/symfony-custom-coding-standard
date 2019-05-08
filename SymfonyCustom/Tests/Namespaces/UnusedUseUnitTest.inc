<?php

namespace MyProject;

use BarClass as Bar;
use Foo;
use Route;
use ORM;
use Truc;
use Machin;
use Unused;
use Client;
use Clients;
use PasClient;
use Somewhere\Something;
use Somewhere\SomethingElse;

class Container
{
    /**
     * @Foo
     * @Route("/{id}")
     * @ORM\Column(type="integer")
     *
     * @param Toto $toto
     * @var Truc $truc
     * @return Machin|\Machine
     */
    function test (Bar $bar): Something
    {
        /** @var Client|Clients[]|PasClient $client */
        $client = [$this->somethingElse];

        return $client;
    }
}
