<?php

namespace App\Services;

class QueryManager
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function fetch($str, $params = array())
    {
        return $this->app->make('App\Queries\\'.$str)->fill($params)->fetch();
    }

    public function pagination($str, $params = array())
    {
        return $this->fetch($str, $params);
    }
}
