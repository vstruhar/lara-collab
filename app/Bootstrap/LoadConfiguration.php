<?php

namespace App\Bootstrap;

use Illuminate\Foundation\Bootstrap\LoadConfiguration as FrameworkLoadConfiguration;
use Symfony\Component\Finder\Finder;

class LoadConfiguration extends FrameworkLoadConfiguration
{
    /**
     * Load the framework configuration without its database file.
     *
     * That file reads PDO::MYSQL_ATTR_SSL_CA while it is being loaded. PHP 8.5
     * deprecates that constant, and the application config replaces this file.
     */
    protected function getBaseConfiguration()
    {
        $config = [];

        foreach (Finder::create()->files()->name('*.php')->in(__DIR__.'/../../vendor/laravel/framework/config') as $file) {
            if ($file->getFilename() === 'database.php') {
                continue;
            }

            $config[$file->getBasename('.php')] = require $file->getRealPath();
        }

        return $config;
    }
}
