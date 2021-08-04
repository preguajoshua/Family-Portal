<?php

namespace App\Models\Membership;

use App\Models\Concerns\MembershipBase;

class LoginToken extends MembershipBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logintokens';

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
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'Id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get ID.
     *
     * @return  string
     */
    public function getId()
    {
        return $this->Id;
    }

    /**
     * Get login ID.
     *
     * @return  string
     */
    public function getLoginId()
    {
        return $this->LoginId;
    }

    /**
     * Get application ID.
     *
     * @return  string
     */
    public function getApplicationID()
    {
        return $this->ApplicationId;
    }

    /**
     * Get cluster ID.
     *
     * @return  string
     */
    public function getClusterId()
    {
        return max($this->ClusterId, 1);
    }

    //
    public function logins()
    {
        return $this->belongsTo(Login::class);
    }

    //
    public function validApplicationId()
    {
        $subApplicationUserFamilyPortal = 2;

        return in_array($this->ApplicationId, [
            UserApplication::APP_FAMILY_PORTAL,
            $subApplicationUserFamilyPortal,
        ]);
    }
}
