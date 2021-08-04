<?php

namespace App\Models;

use Exception;
use App\Models\Concerns\UsesUuid;
use App\Models\Concerns\AppDbBase;
use App\Models\Membership\UserApplication;
use App\Models\Membership\AgencyApplications;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends AppDbBase
{
    use HasFactory, UsesUuid;

    /**
     * The users that belong to the account.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    //
    public static function createAccountOnFamilyPortal(object $request)
    {
        $agencyId = ($request->application_id == UserApplication::APP_HOSPICE)
            ? self::lookupAgencyId($request->agency_id)
            : $request->agency_id;

        return self::firstOrCreate(
            [
                "agency_id" => $agencyId,
                "application_id" => $request->application_id,
            ],
            [
                "agency_name" => $request->agency_name,
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

    //
    public static function fetchAccountWithAgencyIdAndAppId($agencyId, $appId)
    {
        return self::query()
            ->where('agency_id', $agencyId)
            ->where('application_id', $appId)
            ->first();
    }
}
