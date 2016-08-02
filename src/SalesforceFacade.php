<?php

namespace anewmanjones\laravelSalesforceREST;

use Illuminate\Support\Facades\Facade;

class SalesforceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'salesforce';
    }
}