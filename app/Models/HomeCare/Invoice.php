<?php

namespace App\Models\HomeCare;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'homecare_cluster_xxx';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'managedclaims';

    /**
     * Indicates if the IDs are auto-incrementing.
     * Note: For any primary key that is not an integer you should override the $incrementing property on your Eloquent model to false
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
