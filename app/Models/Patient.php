<?php

namespace App\Models;

use Exception;
use App\Models\Concerns\UsesUuid;
use App\Models\Concerns\AppDbBase;
use App\Exceptions\DuplicatePatientException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends AppDbBase
{
    use HasFactory, UsesUuid;

    //
    public static function createPatientOnFamilyPortal(object $request, $accountUserId)
    {
        if (self::where('emr_patient_id', $request->patient_id)->where('emr_patient_contact_id', $request->patient_contact_id)->exists()) {
            throw new DuplicatePatientException('Duplicate patient and patient contact IDs not allowed.');
        }

        return self::create([
            "account_user_id" => $accountUserId,
            "emr_patient_id" => $request->patient_id,
            "emr_patient_contact_id" => $request->patient_contact_id,
        ]);
    }

    //
    public static function fetchPatient($patientId, $patientContactId)
    {
        return self::where([
            "emr_patient_id" => $patientId,
            'emr_patient_contact_id' => $patientContactId,
        ])->first();
    }

    //
    public static function updatePatientAccountUserId($patientId, $patientContactId, $oldAccountUserId, $newAccountUserId)
    {
        return self::query()
            ->where("emr_patient_id", $patientId)
            ->where("emr_patient_contact_id", $patientContactId)
            ->update(["account_user_id" =>  $newAccountUserId]);
    }
}
