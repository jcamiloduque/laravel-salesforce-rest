<?php

namespace anewmanjones\laravelSalesforceREST;

use Illuminate\Support\ServiceProvider;

class SalesforceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Config/salesforce.php' => config_path('salesforce.php'),
        ]);
    }

    public function register()
    {
        $this->app->singleton('salesforce', function($app) {
            return new Salesforce($app['config']->get('salesforce'));
        });
    }
}