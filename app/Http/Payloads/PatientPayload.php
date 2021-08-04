<?php

namespace App\Http\Payloads;

use Spatie\DataTransferObject\DataTransferObject;

class PatientPayload extends DataTransferObject
{
    public string $Id;

    public string $ContactId;

    public string $AgencyId;

    public string $AgencyLocationId;

    public string $Emr;

    public string $FirstName;

    public ?string $MiddleInitial;

    public string $LastName;

    public string $Gender;

    public string $DOB;

    public string $PrimaryPhone;

    public ?string $SecondaryPhone;

    public ?string $EmailAddress;

    public string $StartofCareDate;

    public ?string $PhotoId;

    public bool $isPayor;

    public bool $isAgencyBankAccountSetup;

    public bool $canViewDocumentation;

    public static function fromHomeHealthApi($response): self
    {
        return new self([
            'Id' => $response->Id,
            'ContactId' => $response->ContactId,
            'AgencyId' => $response->AgencyId,
            'AgencyLocationId' => $response->AgencyLocationId,
            'Emr' => 'Home Health',
            'FirstName' => $response->FirstName,
            'MiddleInitial' => $response->MiddleInitial,
            'LastName' => $response->LastName,
            'Gender' => $response->Gender,
            'DOB' => $response->DateOfBirth,
            'PrimaryPhone' => $response->PhoneHome,
            'SecondaryPhone' => $response->PhoneMobile,
            'EmailAddress' => $response->EmailAddress,
            'StartofCareDate' => $response->StartOfCareDate,
            'PhotoId' => $response->PhotoId,
            'isPayor' => false,
            'isAgencyBankAccountSetup' => false,
            'canViewDocumentation' => false,
        ]);
    }

    public static function fromHomeCareApi($response): self
    {
        return new self([
            'Id' => $response->id,
            'ContactId' => $response->patientContactId,
            'AgencyId' => $response->agencyId,
            'AgencyLocationId' => $response->agencyLocationId,
            'Emr' => 'Home Care',
            'FirstName' => $response->firstName,
            'MiddleInitial' => $response->middleInitial,
            'LastName' => $response->lastName,
            'Gender' => $response->gender,
            'DOB' => $response->dateOfBirth,
            'PrimaryPhone' => $response->primaryPhone,
            'SecondaryPhone' => $response->secondaryPhone,
            'EmailAddress' => $response->emailAddress,
            'StartofCareDate' => $response->startOfCareDate,
            'PhotoId' => $response->photoId,
            'isPayor' => $response->isPayor,
            'isAgencyBankAccountSetup' => $response->isAgencyBankAccountSetup,
            'canViewDocumentation' => $response->canViewDocumentation,
        ]);
    }

    public static function fromHospiceApi($response): self
    {
        return new self([
            'Id' => $response->id,
            'ContactId' => $response->patientContactId,
            'AgencyId' => $response->agencyId,
            'AgencyLocationId' => $response->agencyLocationId,
            'Emr' => 'Hospice',
            'FirstName' => $response->firstName,
            'MiddleInitial' => $response->middleInitial,
            'LastName' => $response->lastName,
            'Gender' => $response->gender,
            'DOB' => $response->dateOfBirth,
            'PrimaryPhone' => $response->primaryPhone,
            'SecondaryPhone' => $response->secondaryPhone,
            'EmailAddress' => $response->emailAddress,
            'StartofCareDate' => $response->startOfCareDate,
            'PhotoId' => $response->photoId,
            'isPayor' => false,
            'isAgencyBankAccountSetup' => false,
            'canViewDocumentation' => false,
        ]);
    }
}
