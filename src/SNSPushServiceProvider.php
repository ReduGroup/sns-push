<?php

namespace SNSPush;

use Illuminate\Support\ServiceProvider;

class SNSPushServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('sns-push', function () {
            return new SNSPush(config('services.sns'));
        });
    }

    /**
     * Tell what services this package provides.
     *
     * @return array
     */
    public function provides()
    {
        return [SNSPush::class];
    }
}