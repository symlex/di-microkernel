<?php

namespace DIMicroKernel\Tests\Symfony;

use PHPUnit\Framework\TestCase;
use DIMicroKernel\Symfony\Kernel;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class KernelCacheTest extends TestCase
{
    public function testCaching()
    {
        $kernel = new Kernel('dimicrokernel_test', __DIR__ . '/Kernel', false);
        $result = $kernel->getContainer();
        $this->assertInstanceOf('\Symfony\Component\DependencyInjection\Container', $result);

        $filename = $kernel->getContainerCacheFilename();
        $this->assertFileExists($filename);

        $kernelCached = new Kernel('dimicrokernel_test', __DIR__ . '/Kernel', false);
        $this->assertInstanceOf('\Symfony\Component\DependencyInjection\Container', $kernelCached->getContainer());

        unlink($filename);
    }

    public function testCachingDisabled()
    {
        $kernel = new Kernel('dimicrokernel_test_nocache', __DIR__ . '/Kernel', false);
        $result = $kernel->getContainer();
        $this->assertInstanceOf('\Symfony\Component\DependencyInjection\Container', $result);
        $this->assertFileNotExists($kernel->getContainerCacheFilename());
    }
}