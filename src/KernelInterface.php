<?php

namespace DIMicroKernel;

use Psr\Container\ContainerInterface;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @license MIT
 */
interface KernelInterface
{
    /**
     * Executes the app configured as 'app' in the service container
     *
     * @return mixed
     */
    public function run();

    /**
     * Returns container instance - only for use by framework/kernel code (IoC)
     *
     * Note: No PHP type hinting until Psr\Container\ContainerInterface is generally supported
     *
     * @return ContainerInterface
     */
    public function getContainer();

    /**
     * Returns environment name, e.g. console, web,...
     *
     * @return string
     */
    public function getEnvironment(): string;

    /**
     * Returns sub environment name, e.g. local, test, production,...
     *
     * @return string
     */
    public function getSubEnvironment(): string;

    /**
     * Returns app config parameters like app.name, app.version and app.base_path as array
     *
     * @return array
     */
    public function getAppParameters(): array;

    /**
     * Returns application name, e.g. App
     *
     * @return string
     */
    public function getName(): string;

    public function setName(string $appName);

    /**
     * Returns application version, e.g. 1.0
     *
     * @return string
     */
    public function getVersion(): string;

    public function setVersion(string $appVersion);

    /**
     * Returns character set, e.g. UTF-8
     *
     * @return string
     */
    public function getCharset(): string;

    public function setCharset(string $charSet);

    /**
     * Returns app path, e.g. /var/www/app
     *
     * @return string
     */
    public function getAppPath(): string;

    public function setAppPath(string $appPath);

    /**
     * Returns config path, e.g. /var/www/app/config
     *
     * @return string
     */
    public function getConfigPath(): string;

    public function setConfigPath(string $configPath);

    /**
     * Returns base path, e.g. /var/www
     *
     * @return string
     */
    public function getBasePath(): string;

    public function setBasePath(string $basePath);

    /**
     * Returns storage path, e.g. /var/www/storage
     *
     * @return string
     */
    public function getStoragePath(): string;

    public function setStoragePath(string $storagePath);

    /**
     * Returns log path, e.g. /var/www/storage/logs
     *
     * @return string
     */
    public function getLogPath(): string;

    public function setLogPath(string $logPath);

    /**
     * Returns app path, e.g. /var/www/storage/cache
     *
     * @return string
     */
    public function getCachePath(): string;

    public function setCachePath(string $cachePath);

    /**
     * Returns source code path e.g. /var/www/src
     *
     * @return string
     */
    public function getSrcPath(): string;

    public function setSrcPath(string $srcPath);
}