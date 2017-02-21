A versatile DI micro-kernel for PHP applications
================================================

[![Build Status](https://travis-ci.org/lastzero/di-microkernel.png?branch=master)](https://travis-ci.org/lastzero/di-microkernel)
[![Latest Stable Version](https://poser.pugx.org/lastzero/di-microkernel/v/stable.svg)](https://packagist.org/packages/lastzero/di-microkernel)
[![Total Downloads](https://poser.pugx.org/lastzero/di-microkernel/downloads.svg)](https://packagist.org/packages/lastzero/di-microkernel)
[![License](https://poser.pugx.org/lastzero/di-microkernel/license.svg)](https://packagist.org/packages/lastzero/di-microkernel)

*Note: To see a complete framework based on the micro-kernel please go to https://github.com/lastzero/symlex*

This library contains a micro-kernel that for bootstrapping almost any PHP application, 
including **Silex**, **Symfony Console** and **Lumen**.
It's just about 300 lines of code, initializes the Symfony service container and then starts the app by calling `run()`:

```php
<?php
namespace DIMicroKernel\Symfony;

class Kernel
{
    protected $environment;
    protected $debug;
    protected $appPath;

    public function __construct($environment = 'app', $appPath = '', $debug = false)
    {
        $this->environment = $environment;
        $this->debug = $debug;
        $this->appPath = $appPath;

        $this->init();
    }
    
    ...
    
    public function getApplication()
    {
        if($this->appIsUninitialized()) {
            $this->setUp();
        }

        $result = $this->getContainer()->get('app');

        $this->appInitialized = true;

        return $result;
    }
    
    public function run()
    {
        $arguments = func_get_args();
        $application = $this->getApplication();

        return call_user_func_array(array($application, 'run'), $arguments);
    }
}
```

YAML files located in `$appPath/config/` configure the entire system via dependecy injection. The filename matches the application's environment name (e.g. `console.yml`). These files are in the same format you know from Symfony. In addition to the regular services, they also contain the actual application as a service ("app"):

    services:
        app:
            class: Symfony\Component\Console\Application

This provides a uniform approach for bootstrapping Web applications like `Silex\Application` or command-line 
applications like `Symfony\Component\Console\Application` using the same kernel.

The kernel base class can be extended to customize it for a specific purpose:

```php
<?php

use DIMicroKernel\Symfony\Kernel;

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

Creating a kernel instance and calling run() is enough to start an application:

```php
#!/usr/bin/env php
<?php

require_once 'vendor/autoload.php';

$app = new ConsoleKernel ('console', __DIR__, false);
$app->run();
```

**Caching**

If debug mode is turned off, the service container configuration is cached by the kernel. You have to delete all cache files after updating the configuration. To disable caching completely, add `container.cache: false` to your configuration parameters: 

```yaml
parameters:
    container.cache: false
```