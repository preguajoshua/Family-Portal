<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Illuminate\Events\Dispatcher
 */
class Api extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'api.manager';
	}
}
