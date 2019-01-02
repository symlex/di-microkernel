# Micro-Kernel for PHP Applications

[![Latest Stable Version](https://poser.pugx.org/symlex/di-microkernel/v/stable.svg)](https://packagist.org/packages/symlex/di-microkernel)
[![License](https://poser.pugx.org/symlex/di-microkernel/license.svg)](https://packagist.org/packages/symlex/di-microkernel)
[![Test Coverage](https://codecov.io/gh/symlex/di-microkernel/branch/master/graph/badge.svg)](https://codecov.io/gh/symlex/di-microkernel)
[![Build Status](https://travis-ci.org/symlex/di-microkernel.png?branch=master)](https://travis-ci.org/symlex/di-microkernel)
[![Documentation](https://readthedocs.org/projects/symlex-docs/badge/?version=latest&style=flat)](https://docs.symlex.org/en/latest/di-microkernel)

This library contains a micro-kernel for bootstrapping almost any PHP application, including [Silex](https://silex.symfony.com/),
[Symlex](https://github.com/symlex/symlex) (a framework stack for agile Web development based on Symfony) 
and [Symfony Console](https://symfony.com/doc/current/components/console.html). 
The kernel itself is just a few lines to set a bunch of environment parameters and create a service container 
instance with that.

![Micro-Kernel Architecture](https://docs.symlex.org/en/latest/di-microkernel/img/architecture.svg)

## Run an App ##

Creating a kernel instance and calling `run()` is enough to start an application:

```php
#!/usr/bin/env php
<?php

// Composer
require_once 'vendor/autoload.php'; 

$app = new \DIMicroKernel\Kernel('console');

// Run the 'app' service defined in config/console.yml
$app->run(); 
```

## Configuration ##

YAML files located in `config/` configure the application and all of it's dependencies as a service. The filename matches 
the application's environment name (e.g. `config/console.yml`). The configuration can additionally be modified 
for sub environments such as local or production by providing a matching config file like `config/console.local.yml`
(see `app.sub_environment` parameter). These files are in the same [well documented](https://symfony.com/doc/current/components/dependency_injection.html) format you might know from Symfony:

```yaml
parameters:
    app.name: 'My App'
    app.version: '1.0'

services:
    doctrine.migrations.migrate:
        class: Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand
        
    app:
        class: Symfony\Component\Console\Application
        public: true
        arguments: [%app.name%, %app.version%]
        calls:
            - [ add, [ "@doctrine.migrations.migrate" ] ]
```

This provides a uniform approach for bootstrapping Web applications like `Silex\Application`,
`Symlex\Application\Web` or command-line applications like `Symfony\Component\Console\Application` using the same kernel. 
The result is much cleaner and leaner than the usual bootstrap and configuration madness you know from many frameworks.

## Parameters ##

The kernel sets a number of default parameters that can be used for configuring services. The default values can be 
changed via setter methods of the kernel or overwritten/extended by container config files 
and environment variables (e.g. `url: '%env(DATABASE_URL)%'`).

Parameter           | Getter method         | Setter method         | Default value            
--------------------|-----------------------|-----------------------|------------------
app.name            | getName()             | setName()             | 'Kernel'
app.version         | getVersion()          | setVersion()          | '1.0'
app.environment     | getEnvironment()      | setEnvironment()      | 'app'
app.sub_environment | getSubEnvironment()   | setSubEnvironment()   | 'local'
app.debug           | isDebug()             | setDebug()            | false
app.charset         | getCharset()          | setCharset()          | 'UTF-8'
app.path            | getAppPath()          | setAppPath()          | './'
app.config_path     | getConfigPath()       | setConfigPath()       | './config'
app.base_path       | getBasePath()         | setBasePath()         | '../'
app.storage_path    | getStoragePath()      | setStoragePath()      | '../storage'
app.log_path        | getLogPath()          | setLogPath()          | '../storage/log'
app.cache_path      | getCachePath()        | setCachePath()        | '../storage/cache'
app.src_path        | getSrcPath()          | setSrcPath()          | '../src'

## Customization ##

The kernel base class can be extended to customize it for a specific purpose such as long running console applications:

```php
<?php

use DIMicroKernel\Kernel;

class ConsoleApp extends Kernel
{
    public function __construct($appPath, $debug = false)
    {
        parent::__construct('console', $appPath, $debug);
    }

    public function setUp()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
    }
}
```

## Caching ##

If debug mode is turned off, the service container configuration is cached by the kernel in the directory set as cache path. You have to delete all cache files after updating the configuration. To disable caching completely, add `container.cache: false` to your configuration parameters: 

```yaml
parameters:
    container.cache: false
```

## Composer ##

To use this library in your project, simply run `composer require symlex/di-microkernel` or
add "symlex/di-microkernel" to your [composer.json](https://getcomposer.org/doc/04-schema.md) file and run `composer update`:

```json
{
    "require": {
        "php": ">=7.1",
        "symlex/di-microkernel": "^2.0"
    }
}
```

## About ##

DIMicroKernel is maintained by [Michael Mayer](https://blog.liquidbytes.net/about/).
Feel free to send an e-mail to [hello@symlex.org](mailto:hello@symlex.org) if you have any questions, 
need [commercial support](https://blog.liquidbytes.net/contact/) or just want to say hello. 
We welcome contributions of any kind. If you have a bug or an idea, read our 
[guide](CONTRIBUTING.md) before opening an issue.
