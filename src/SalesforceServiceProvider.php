<?php

namespace anewmanjones\laravelSalesforceREST;

use Illuminate\Support\ServiceProvider;

class SalesforceServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Config/salesforce.php' => config_path('salesforce.php'),
        ]);
    }

    public function register()
    {
        $this->app->singleton(Salesforce::class, function($app) {
            return new Salesforce($app['config']->get('salesforce'));
        });
    }

    public function provides()
    {
        return [
            Salesforce::class
        ];
    }
}