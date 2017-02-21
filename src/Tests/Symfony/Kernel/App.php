<?php

namespace DIMicroKernel\Tests\Symfony\Kernel;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class App
{
    public function run () {
        return func_get_args();
    }
}