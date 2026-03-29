<?php

namespace CyberShield\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use CyberShield\Providers\CyberShieldServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            CyberShieldServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set up default config
        $app['config']->set('app.key', 'base64:u6tXN/Mv8g3yYVv1uXqZ2n8w9j1k2l3m4n5o6p7q8r9=');
        $app['config']->set('cybershield.logging.enabled', true);
        $app['config']->set('cybershield.logging.channels.request', true);
        $app['config']->set('cybershield.logging.format', '[{datetime}] {level} {ip} {user_id} {method} {url} {status} {message}');
        $app['config']->set('cybershield.threat_detection.block_on_threat', false);
    }
}
