<?php

namespace DIMicroKernel;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use ProjectServiceContainer as CachedContainer;
use DIMicroKernel\Exception\ContainerNotFoundException;
use DIMicroKernel\Exception\Exception;

/**
 * This micro-kernel can be used for bootstrapping almost any PHP application.
 * It can be extended to customize it for a specific purpose, e.g. command line applications.
 *
 * Note: To see a complete framework based on the micro-kernel go to https://github.com/lastzero/symlex
 *
 * @see https://github.com/lastzero/di-microkernel/blob/master/README.md
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class Kernel
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;
    protected $environment;
    protected $appPath;
    protected $basePath;
    protected $storagePath;
    protected $srcPath;
    protected $configPath;
    protected $logPath;
    protected $cachePath;
    protected $charSet;
    protected $debug;
    protected $name;
    protected $version = '1.0';
    protected $appInitialized = false;

    /**
     * Kernel constructor.
     *
     * @param string $environment e.g. console tells the kernel to load the service config from config/console.yml
     * @param string $appPath e.g. __DIR__
     * @param bool $debug service container is not cached, if true
     */
    public function __construct($environment = 'app', $appPath = '', $debug = false)
    {
        $this->environment = $environment;
        $this->debug = $debug;
        $this->appPath = $appPath;

        $this->init();
    }

    /**
     * Executes the app configured as 'app' in the service container
     *
     * @return mixed
     */
    public function run()
    {
        $arguments = func_get_args();

        return $this->__call('run', $arguments);
    }

    /**
     * Returns the container instance (automatically creates an instance, if none exists)
     *
     * @return ContainerBuilder
     * @throws ContainerNotFoundException
     */
    public function getContainer()
    {
        if (!$this->container) {
            $this->boot();
        }

        return $this->container;
    }

    public function setContainer(Container $container)
    {
        if ($this->container instanceof Container) {
            throw new Exception('Container already set');
        }

        $this->container = $container;
    }

    /**
     * Returns application name, e.g. App
     *
     * @return string
     */
    public function getName(): string
    {
        if (null === $this->name) {
            $this->name = ucfirst(preg_replace('/[^a-zA-Z0-9_]+/', '', basename($this->getAppPath())));
        }

        return $this->name;
    }

    public function setName(string $appName)
    {
        $this->name = $appName;
    }

    /**
     * Returns application version, e.g. 1.0
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $appVersion)
    {
        $this->version = $appVersion;
    }

    /**
     * Returns environment name, e.g. console, web,...
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Returns sub environment name, e.g. local, test, production,...
     *
     * Use for overwriting the default service configuration e.g. with console.local.yml
     *
     * @return string
     */
    public function getSubEnvironment(): string
    {
        $result = 'local';

        if ($this->container && $this->container->hasParameter('app.sub_environment')) {
            $result = (string)$this->container->getParameter('app.sub_environment');
        }

        return $result;
    }

    /**
     * Returns character set, e.g. UTF-8
     *
     * @return string
     */
    public function getCharset(): string
    {
        if ($this->charSet == '') {
            $this->setCharset('UTF-8');
        }

        return $this->charSet;
    }

    public function setCharset(string $charSet)
    {
        $this->charSet = $charSet;
    }

    /**
     * Returns app path, e.g. /var/www/app
     *
     * @return string
     */
    public function getAppPath(): string
    {
        if ($this->appPath == '') {
            $r = new \ReflectionObject($this);
            $this->setAppPath(str_replace('\\', '/', dirname($r->getFileName())));
        }

        return $this->appPath;
    }

    public function setAppPath(string $appPath)
    {
        $this->appPath = $appPath;
    }

    /**
     * Returns config path, e.g. /var/www/app/config
     *
     * @return string
     */
    public function getConfigPath(): string
    {
        if ($this->configPath == '') {
            $this->setConfigPath($this->getAppPath() . '/config');
        }

        return $this->configPath;
    }

    public function setConfigPath(string $configPath)
    {
        $this->configPath = $configPath;
    }

    /**
     * Returns base path, e.g. /var/www
     *
     * @return string
     */
    public function getBasePath(): string
    {
        if ($this->basePath == '') {
            $this->setBasePath(realpath($this->getAppPath() . '/..'));
        }

        return $this->basePath;
    }

    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Returns storage path, e.g. /var/www/storage
     *
     * @return string
     */
    public function getStoragePath(): string
    {
        if ($this->storagePath == '') {
            $this->setStoragePath(realpath($this->getBasePath() . '/storage'));
        }

        return $this->storagePath;
    }

    public function setStoragePath(string $storagePath)
    {
        $this->storagePath = $storagePath;
    }

    /**
     * Returns log path, e.g. /var/www/storage/logs
     *
     * @return string
     */
    public function getLogPath(): string
    {
        if ($this->logPath == '') {
            $this->setLogPath(realpath($this->getStoragePath() . '/log'));
        }

        return $this->logPath;
    }

    public function setLogPath(string $logPath)
    {
        $this->logPath = $logPath;
    }

    /**
     * Returns app path, e.g. /var/www/storage/cache
     *
     * @return string
     */
    public function getCachePath(): string
    {
        if ($this->cachePath == '') {
            $this->setCachePath(realpath($this->getStoragePath() . '/cache'));
        }

        return $this->cachePath;
    }

    public function setCachePath(string $cachePath)
    {
        $this->cachePath = $cachePath;
    }

    /**
     * Returns source code path e.g. /var/www/src
     *
     * @return string
     */
    public function getSrcPath(): string
    {
        if ($this->srcPath == '') {
            $this->setSrcPath(realpath($this->getBasePath() . '/src'));
        }

        return $this->srcPath;
    }

    public function setSrcPath(string $srcPath)
    {
        $this->srcPath = $srcPath;
    }

    /**
     * Returns app config parameters like app.name, app.version and app.base_path as array
     *
     * @return array
     */
    public function getAppParameters(): array
    {
        return array(
            'app.name' => $this->getName(),
            'app.version' => $this->getVersion(),
            'app.environment' => $this->environment,
            'app.sub_environment' => $this->getSubEnvironment(),
            'app.debug' => $this->debug,
            'app.charset' => $this->getCharset(),
            'app.path' => $this->getAppPath(),
            'app.base_path' => $this->getBasePath(),
            'app.storage_path' => $this->getStoragePath(),
            'app.src_path' => $this->getSrcPath(),
            'app.cache_path' => $this->getCachePath(),
            'app.log_path' => $this->getLogPath(),
            'app.config_path' => $this->getConfigPath(),
        );
    }

    /**
     * Returns container cache filename, e.g. /var/www/storage/cache/container_8a4ba7589ab6aba0b5.php
     *
     * @return string
     */
    public function getContainerCacheFilename()
    {
        $environment = $this->getEnvironment();
        $appPath = $this->getAppPath();

        $filename = $this->getCachePath() . '/container_' . md5($environment . $appPath) . '.php';

        return $filename;
    }

    /**
     * Returns true, if the container.cache parameter does not exist or is false
     *
     * @return bool
     */
    public function containerIsCacheable()
    {
        $result = true; // container is cacheable by default

        if ($this->container->hasParameter('container.cache')) {
            $result = (bool)$this->container->getParameter('container.cache');
        }

        return $result;
    }

    /**
     * Calls a method of the app service
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $application = $this->getApplication();

        return call_user_func_array(array($application, $name), $arguments);
    }

    /**
     * Optional code to be executed, after the kernel constructor
     */
    protected function init()
    {
        // Optional
    }

    /**
     * Returns true, if a container instance exists
     *
     * @return bool
     */
    protected function hasBooted()
    {
        $result = $this->container instanceof Container;

        return $result;
    }

    /**
     * Creates a container instance
     */
    protected function boot()
    {
        if ($this->hasBooted()) return; // Nothing to do

        if ($this->debug) {
            $this->setContainer(new ContainerBuilder(new ParameterBag($this->getAppParameters())));
            $this->loadContainerConfiguration();
        } else {
            $filename = $this->getContainerCacheFilename();

            if (file_exists($filename)) {
                require_once($filename);
                $this->setContainer(new CachedContainer());
            } else {
                $this->setContainer(new ContainerBuilder(new ParameterBag($this->getAppParameters())));
                $this->loadContainerConfiguration();
                $this->container->compile();

                if ($this->containerIsCacheable()) {
                    $dumper = new PhpDumper($this->container);
                    file_put_contents($filename, $dumper->dump());
                }
            }
        }
    }

    /**
     * Loads the container config from YAML files in the config directory
     */
    protected function loadContainerConfiguration()
    {
        $configPath = $this->getConfigPath();
        $environment = $this->getEnvironment();

        $loader = new YamlFileLoader($this->container, new FileLocator($configPath));

        if (file_exists($configPath . '/' . $environment . '.yml')) {
            $loader->load($environment . '.yml');
        }

        $subEnvironment = $this->getSubEnvironment();

        if (file_exists($configPath . '/' . $environment . '.' . $subEnvironment . '.yml')) {
            $loader->load($environment . '.' . $subEnvironment . '.yml');
        }
    }

    /**
     * Returns true, if setUp() was not called already
     * @return bool
     */
    protected function appIsUninitialized()
    {
        return !$this->appInitialized;
    }

    /**
     * Returns the app service from the container and calls setUp() once, if not done yet
     *
     * @return object
     */
    protected function getApplication()
    {
        if ($this->appIsUninitialized()) {
            $this->setUp();
        }

        $result = $this->getContainer()->get('app');

        $this->appInitialized = true;

        return $result;
    }

    /**
     * Optional code to be executed, before the app instance is created
     */
    protected function setUp()
    {
        // Optional
    }
}