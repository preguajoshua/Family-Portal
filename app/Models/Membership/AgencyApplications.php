<?php

namespace App\Models\Membership;

use App\Models\Concerns\MembershipBase;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgencyApplications extends MembershipBase
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'agencyapplications';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

}
