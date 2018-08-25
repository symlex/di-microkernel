A micro-kernel for PHP applications
===================================

[![Build Status](https://travis-ci.org/symlex/di-microkernel.png?branch=master)](https://travis-ci.org/symlex/di-microkernel)
[![Latest Stable Version](https://poser.pugx.org/symlex/di-microkernel/v/stable.svg)](https://packagist.org/packages/symlex/di-microkernel)
[![License](https://poser.pugx.org/symlex/di-microkernel/license.svg)](https://packagist.org/packages/symlex/di-microkernel)

This library contains a micro-kernel for bootstrapping almost any PHP application, including 
[Symlex](https://github.com/symlex/symlex) (a complete framework stack for agile Web development based on Symfony and Vue.js), 
[Silex](https://silex.symfony.com/) and [Symfony Console](https://symfony.com/doc/current/components/console.html). 
The kernel itself is just about 400 lines of code to set a bunch of default parameters for your application and create a 
service container instance with that.

Creating a kernel instance and calling `run()` is enough to start your application:

```php
#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php'; // Composer

$app = new \DIMicroKernel\Kernel('console');

$app->run(); // runs the 'app' service defined in config/console.yml
```

Configuration
-------------

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

Default parameters
------------------

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

Caching
-------

If debug mode is turned off, the service container configuration is cached by the kernel in the directory set as cache path. You have to delete all cache files after updating the configuration. To disable caching completely, add `container.cache: false` to your configuration parameters: 

```yaml
parameters:
    container.cache: false
```

Composer
--------

If you are using composer, simply add "symlex/di-microkernel" to your composer.json file and run composer update:

```json
"require": {
    "symlex/di-microkernel": "^2.0"
}
```
