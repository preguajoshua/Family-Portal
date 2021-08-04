<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory, UsesUuid;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id',
        'FirstName',
        'LastName',
        'PatientIdNumber',
        'AgencyId',
        'AgencyLocationId',
        'Gender',
        'PhotoId',
        'StartofCareDate',
        'DOB',
        'ContactId',
        'isPayor',
        'isAgencyBankAccountSetup',
        'canViewDocumentation',
    ];
}
