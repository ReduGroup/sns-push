<?php

namespace SNSPush;

use Illuminate\Support\ServiceProvider;

class SNSPushServiceProvider extends ServiceProvider
{
    /**
     * The package name.
     *
     * @var string
     */
    private $packageName = 'sns-push';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // ...
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind($this->packageName, SNSPush::class);
    }
}