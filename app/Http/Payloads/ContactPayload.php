<?php

namespace App\Http\Payloads;

use Spatie\DataTransferObject\DataTransferObject;

class ContactPayload extends DataTransferObject
{
    public string $Id;

    public string $FirstName;

    public string $LastName;

    public string $Relationship;

    public string $PhoneHome;

    public ?string $EmailAddress;

    public bool $IsPrimary;

    public bool $IsPayor;

    public static function fromHomeHealthApi($response): self
    {
        return new self([
            'Id' => $response->Id,
            'FirstName' => $response->FirstName,
            'LastName' => $response->LastName,
            'Relationship' => $response->Relationship,
            'PhoneHome' => $response->PhoneHome,
            'EmailAddress' => $response->EmailAddress,
            'IsPrimary' => $response->IsPrimary,
            'IsPayor' => false,
        ]);
    }

    public static function fromHomeCareApi($response): self
    {
        return new self([
            'Id' => $response->id,
            'FirstName' => $response->firstName,
            'LastName' => $response->lastName,
            'Relationship' => $response->relationship,
            'PhoneHome' => $response->phoneHome,
            'EmailAddress' => $response->emailAddress,
            'IsPrimary' => $response->isPrimary,
            'IsPayor' => $response->isPayor,
        ]);
    }

    public static function fromHospiceApi($response): self
    {
        return new self([
            'Id' => $response->id,
            'FirstName' => $response->firstName,
            'LastName' => $response->lastName,
            'Relationship' => $response->relationship,
            'PhoneHome' => $response->phoneHome,
            'EmailAddress' => $response->emailAddress,
            'IsPrimary' => $response->isPrimary,
            'IsPayor' => false,
        ]);
    }
}
