<?php

namespace App\Models\Membership;

use Illuminate\Support\Str;
use App\Models\Concerns\MembershipBase;

class Login extends MembershipBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logins';

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
     * No roles.
     *
     * @var integer
     */
    const ROLE_NONE = 0;

    /**
     * Application user role.
     *
     * @var integer
     */
    const ROLE_APPLICATION_USER = 1;

    /**
     * Sub-application user role.
     *
     * @var integer
     */
    const ROLE_SUB_APPLICATION_USER = 2;

    /**
     * Determine if the roles contains sub-application user.
     *
     * @return  boolean
     */
    public function hasRole($role)
    {
        return ($this->LoginRoles & $role);
    }

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
     * Get the first record matching the attributes or create it.
     *
     * @param   array  $attributes
     * @return  \App\Models\Membership\Login
     */
    public static function firstOrCreateLoginFromUser($attributes)
    {
        return self::firstOrCreate(
            [
                'EmailAddress' => $attributes['email'],
            ],
            [
                'Id' => (string) Str::uuid(),
                'DisplayName' => $attributes['name'],
                'Role' => self::ROLE_NONE,
                'LoginRoles' => self::ROLE_SUB_APPLICATION_USER,
                'LastLoginDate' => now(),
                'IsActive' => true,
                'IsLocked' => false,
                'IsAxxessAdmin' => false,
                'IsAxxessSupport' => false,
                'Created' => today(),
            ]
        );
    }

    /**
     * Grant sub-application role.
     *
     * @return  void
     */
    public function grantSubApplicationRole()
    {
        if ($this->hasRole(self::ROLE_SUB_APPLICATION_USER)) {
            return;
        }

        $this->update([
            'LoginRoles' => (self::ROLE_APPLICATION_USER | self::ROLE_SUB_APPLICATION_USER),
        ]);
    }
}
