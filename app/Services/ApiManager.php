<?php

namespace App\Services;

class ApiManager {

	private $app;

	public function __construct($app)
	{
		$this->app = $app;
	}

	public function request($str, $params = array())
	{
		return $this->app->make('App\Api\\'.$str)->fill($params)->fetch();
	}

}