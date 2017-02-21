<?php

namespace DIMicroKernel\Tests\Kernel;

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