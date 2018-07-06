<?php

namespace DIMicroKernel\Tests;

use TestTools\TestCase\UnitTestCase;
use DIMicroKernel\Kernel;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class KernelTest extends UnitTestCase
{
    /**
     * @var Kernel
     */
    protected $kernel;

    public function setUp()
    {
        $this->kernel = new Kernel('dimicrokernel_test', __DIR__ . '/Kernel', true);
    }

    public function testGetName()
    {
        $result = $this->kernel->getName();
        $this->assertEquals('Kernel', $result);
    }

    public function testGetVersion()
    {
        $result = $this->kernel->getVersion();
        $this->assertEquals('1.0', $result);
    }

    public function testGetEnvironment()
    {
        $result = $this->kernel->getEnvironment();
        $this->assertEquals('dimicrokernel_test', $result);
    }

    public function testGetSubEnvironment()
    {
        $result = $this->kernel->getSubEnvironment();
        $this->assertEquals('local', $result);
    }

    public function testGetCharset()
    {
        $result = $this->kernel->getCharset();
        $this->assertEquals('UTF-8', $result);
    }

    public function testIsDebug()
    {
        $result = $this->kernel->isDebug();
        $this->assertTrue($result);
    }

    public function testGetContainerParameters()
    {
        $_SERVER['APPLICATION_NAME'] = 'YYY';

        $result = $this->kernel->getContainer()->getParameterBag()->all();

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('app.name', $result);
        $this->assertEquals('Kernel', $result['app.name']);
        $this->assertArrayHasKey('app.name2', $result);
        $this->assertEquals('YYY', $result['app.name2']);
        $this->assertArrayHasKey('app.version', $result);
        $this->assertArrayHasKey('app.environment', $result);
        $this->assertArrayHasKey('app.sub_environment', $result);
        $this->assertArrayHasKey('app.debug', $result);
        $this->assertArrayHasKey('app.charset', $result);
        $this->assertArrayHasKey('app.path', $result);
        $this->assertArrayHasKey('app.base_path', $result);
        $this->assertArrayHasKey('app.storage_path', $result);
        $this->assertArrayHasKey('app.cache_path', $result);
        $this->assertArrayHasKey('app.log_path', $result);
        $this->assertArrayHasKey('app.config_path', $result);
    }

    public function testGetContainer()
    {
        $result = $this->kernel->getContainer();

        $this->assertInstanceOf('\Symfony\Component\DependencyInjection\Container', $result);
    }

    public function testRun()
    {
        $result = $this->kernel->run('foo', 'bar');
        $this->assertInternalType('array', $result);
        $expected = array('foo', 'bar');
        $this->assertEquals($expected, $result);
    }
}