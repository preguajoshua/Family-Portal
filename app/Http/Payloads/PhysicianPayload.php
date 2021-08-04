<?php

namespace App\Http\Payloads;

use Spatie\DataTransferObject\DataTransferObject;

class PhysicianPayload extends DataTransferObject
{
    public string $Id;

    public string $FirstName;

    public string $LastName;

    public string $PhoneWork;

    public ?string $EmailAddress;

    public ?string $SpecialtyDescription;

    public bool $isPrimary;

    public static function fromHomeHealthApi($response): self
    {
        return new self([
            'Id' => $response->Id,
            'FirstName' => $response->FirstName,
            'LastName' => $response->LastName,
            'PhoneWork' => $response->PhoneWork,
            'EmailAddress' => $response->EmailAddress,
            'SpecialtyDescription' => 'Unknown', // TODO - $response->Specialty,
            'isPrimary' => $response->IsPrimary,
        ]);
    }

    public static function fromHomeCareApi($response): self
    {
        return new self([
            'Id' => $response->id,
            'FirstName' => $response->firstName,
            'LastName' => $response->lastName,
            'PhoneWork' => $response->phoneWork,
            'EmailAddress' => $response->emailAddress,
            'SpecialtyDescription' => $response->specialty,
            'isPrimary' => $response->isPrimary,
        ]);
    }

    public static function fromHospiceApi($response): self
    {
        return new self([
            'Id' => $response->id,
            'FirstName' => $response->firstName,
            'LastName' => $response->lastName,
            'PhoneWork' => $response->phoneWork,
            'EmailAddress' => $response->emailAddress,
            'SpecialtyDescription' => null,
            'isPrimary' => $response->isPrimary,
        ]);
    }
}
