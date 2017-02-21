<?php

namespace DIMicroKernel\Symfony;

use DIMicroKernel\KernelInterface;
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
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
class Kernel implements KernelInterface
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

    public function __construct($environment = 'app', $appPath = '', $debug = false)
    {
        $this->environment = $environment;
        $this->debug = $debug;
        $this->appPath = $appPath;

        $this->init();
    }

    public function setContainer(Container $container)
    {
        if ($this->container instanceof Container) {
            throw new Exception('Container already set');
        }

        $this->container = $container;
    }

    protected function init()
    {
        // Optional
    }

    protected function hasBooted()
    {
        $result = $this->container instanceof Container;

        return $result;
    }

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

    public function getContainerCacheFilename()
    {
        $environment = $this->getEnvironment();
        $appPath = $this->getAppPath();

        $filename = $this->getCachePath() . '/container_' . md5($environment . $appPath) . '.php';

        return $filename;
    }

    public function containerIsCacheable()
    {
        $result = true; // container is cacheable by default

        if ($this->container->hasParameter('container.cache')) {
            $result = (bool)$this->container->getParameter('container.cache');
        }

        return $result;
    }

    /**
     * "local" is the default sub environment for overwriting the existing config
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

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $appVersion)
    {
        $this->version = $appVersion;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

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

    protected function appIsUninitialized()
    {
        return !$this->appInitialized;
    }

    protected function getApplication()
    {
        if ($this->appIsUninitialized()) {
            $this->setUp();
        }

        $result = $this->getContainer()->get('app');

        $this->appInitialized = true;

        return $result;
    }

    protected function setUp()
    {
        // Optional
    }

    public function __call($name, $arguments)
    {
        $application = $this->getApplication();

        return call_user_func_array(array($application, $name), $arguments);
    }

    public function run()
    {
        $arguments = func_get_args();

        return $this->__call('run', $arguments);
    }
}