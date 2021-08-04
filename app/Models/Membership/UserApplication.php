<?php

namespace App\Models\Membership;

use Exception;
use App\Models\Concerns\UsesUuid;
use App\Models\Concerns\MembershipBase;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserApplication extends MembershipBase
{
    use HasFactory, UsesUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'userapplications';

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
     * Home Health app ID.
     *
     * @var integer
     */
    const APP_HOME_HEALTH = 1;

    /**
     * Home Care app ID.
     *
     * @var integer
     */
    const APP_HOME_CARE = 2;

    /**
     * Hospice app ID.
     *
     * @var integer
     */
    const APP_HOSPICE = 256;

    /**
     * Family portal app ID.
     *
     * @var integer
     */
    const APP_FAMILY_PORTAL = 1024;

    //
    public static function createUserApplicationOnMembership(object $request, $loginId, string $userId)
    {
        $agencyId = ($request->application_id == self::APP_HOSPICE)
            ? self::lookupAgencyId($request->agency_id)
            : $request->agency_id;

        return self::firstOrCreate(
            [
                "UserId" => $userId,
                "LoginId" => $loginId,
                "AgencyId" => $agencyId,
            ],
            [
                "Created" => now(),
                "IsDeprecated" => false,
                "Application" => self::APP_FAMILY_PORTAL,
                "TitleType" => null,
                "Status" => 1,
            ]
        );
    }

    //
    private static function lookupAgencyId($applicationAccountId)
    {
        $agencyApplication = AgencyApplications::query()
            ->select('AgencyId')
            ->where('ApplicationAccountId', $applicationAccountId)
            ->first();

        if (! $agencyApplication) {
            throw new Exception('Can not find Hospice Agency Id');
        }

        return $agencyApplication->AgencyId;
    }
}
