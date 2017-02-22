A micro-kernel for PHP applications
===================================

[![Build Status](https://travis-ci.org/lastzero/di-microkernel.png?branch=master)](https://travis-ci.org/lastzero/di-microkernel)
[![Latest Stable Version](https://poser.pugx.org/lastzero/di-microkernel/v/stable.svg)](https://packagist.org/packages/lastzero/di-microkernel)
[![Total Downloads](https://poser.pugx.org/lastzero/di-microkernel/downloads.svg)](https://packagist.org/packages/lastzero/di-microkernel)
[![License](https://poser.pugx.org/lastzero/di-microkernel/license.svg)](https://packagist.org/packages/lastzero/di-microkernel)

*Note: To see a complete framework based on the micro-kernel please go to https://github.com/lastzero/symlex*

This library contains a micro-kernel for bootstrapping almost any PHP application, including **Silex**, 
**Symfony Console** and **Lumen**. It's just about 300 lines of code, initializes the Symfony service container 
using YAML files and then starts the app by calling `run()`:

```php
<?php

namespace DIMicroKernel;

class Kernel
{
    protected $environment;
    protected $appPath;
    protected $debug;
    protected $container;

    public function __construct(string $environment, string $appPath, bool $debug)
    {
        $this->environment = $environment;
        $this->appPath = $appPath;
        $this->debug = $debug;
    }
    
    ...
    
    public function getContainer()
    {
        if (!$this->container) {
            $this->boot();
        }
        
        return $this->container;
    }
    
    public function getApplication()
    {
        $result = $this->getContainer()->get('app');

        return $result;
    }
    
    public function run()
    {
        $application = $this->getApplication();

        return call_user_func_array(array($application, 'run'), func_get_args());
    }
}
```

YAML files located in `config/` configure the entire system via dependecy injection. The filename matches the 
application's environment name (e.g. `config/console.yml`). These files are in the same format you know from 
Symfony. In addition to the regular services, they also contain the actual application as a service ("app"):

```yaml
parameters:
    app.name: 'My App'
    app.version: '1.0'

services:
    doctrine.migrations.migrate:
        class: Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand
        
    app:
        class: Symfony\Component\Console\Application
        arguments: [%app.name%, %app.version%]
        calls:
            - [ add, [ "@doctrine.migrations.migrate" ] ]
```

This provides a uniform approach for bootstrapping Web applications like `Silex\Application` or command-line 
applications like `Symfony\Component\Console\Application` using the same kernel. The result is much cleaner and 
leaner than the usual bootstrap and configuration madness you know from most frameworks.

The kernel base class can be extended to customize it for a specific purpose:

```php
<?php

use DIMicroKernel\Kernel;

class ConsoleKernel extends Kernel
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

Creating a kernel instance and calling run() is enough to start your application:

```php
#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

$app = new ConsoleKernel ('console', __DIR__, false);
$app->run();
```

Default parameters
------------------

The kernel sets a number of default parameters that can be used for configuring services. The default values can be changed via setter methods of the kernel or overwritten by the container config files.

Parameter           | Getter method         | Setter method         | Default value            
--------------------|-----------------------|-----------------------|------------------
app.name            | getName()             | setName($value)       | App
app.version         | getVersion()          | setVersion($value)    | 1.0
app.environment     | getEnvironment()      |                       | app
app.sub_environment | getSubEnvironment()   |                       | local
app.debug           |                       |                       | false
app.charset         | getCharset()          | setCharset($value)    | UTF-8
app.path            | getAppPath()          | setAppPath($value)    | ./
app.config_path     | getConfigPath()       | setConfigPath($value) | ./config
app.base_path       | getBasePath()         | setBasePath($value)   | ../
app.storage_path    | getStoragePath()      | setStoragePath($value)| ../storage
app.cache_path      | getCachePath()        | setCachePath($value)  | ../storage/cache
app.log_path        | getLogPath()          | setLogPath($value)    | ../storage/log
app.src_path        | getSrcPath()          | setSrcPath($value)    | ../src

Caching
-------

If debug mode is turned off, the service container configuration is cached by the kernel in the directory set as cache path. You have to delete all cache files after updating the configuration. To disable caching completely, add `container.cache: false` to your configuration parameters: 

```yaml
parameters:
    container.cache: false
```

Composer
--------

If you are using composer, simply add "lastzero/di-microkernel" to your composer.json file and run composer update:

```json
"require": {
    "lastzero/di-microkernel": "^1.0"
}
```
