<?php

namespace App\Http\Controllers\Api;

use Spatie\DataTransferObject\DataTransferObject;

class UserApiPayload extends DataTransferObject
{
    public string $login_id;

    public string $agency_id;

    public string $patient_id;

    public string $patient_contact_id;
}
