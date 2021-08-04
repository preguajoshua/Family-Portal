<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

class SushiBase extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Clear the current Sushi connection and reboot.
     *
     * @return  void
     */
    public static function reboot()
    {
        static::setSushiConnection(null);
        static::clearBootedModels();
    }

    /**
     * Set the Sushi connection.
     *
     * @param   string  $connection
     * @return  void
     */
    public static function setSushiConnection($connection)
    {
        static::$sushiConnection = $connection;
    }
}
