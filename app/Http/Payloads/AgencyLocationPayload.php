<?php

namespace App\Http\Payloads;

use Spatie\DataTransferObject\DataTransferObject;

class AgencyLocationPayload extends DataTransferObject
{
    public string $Id;

    public string $AgencyId;

    public string $Name;

    public string $LocationName;

    public string $AddressLine1;

    public ?string $AddressLine2;

    public string $AddressCity;

    public string $AddressStateCode;

    public string $AddressZipCode;

    public ?string $CountyDistrict;

    public bool $showCountyDistrict;

    public string $Country;

    public ?string $PhoneWork;

    public ?string $FaxNumber;

    public bool $IsMainOffice;

    public static function fromHomeHealthApi($response): self
    {
        return new self([
            'Id' => $response->Id,
            'AgencyId' => $response->AgencyId,
            'Name' => $response->Name,
            'LocationName' => $response->LocationName,
            'AddressLine1' => $response->AddressLine1,
            'AddressLine2' => null,
            'AddressCity' => $response->AddressCity,
            'AddressStateCode' => $response->AddressStateCode,
            'AddressZipCode' => $response->AddressZipCode,
            'CountyDistrict' => $response->CountyDistrict,
            'showCountyDistrict' => ($response->CountyDistrict) ? true : false,
            'Country' => 'USofA', //$response->Country, // TODO
            'PhoneWork' => $response->PhoneWork,
            'FaxNumber' => $response->FaxNumber,
            'IsMainOffice' => $response->IsMainOffice,
        ]);
    }

    public static function fromHomeCareApi($response): self
    {
        return new self([
            'Id' => $response->id,
            'AgencyId' => $response->agencyId,
            'Name' => $response->agencyName,
            'LocationName' => $response->locationName,
            'AddressLine1' => $response->addressLine1,
            'AddressLine2' => $response->addressLine2,
            'AddressCity' => $response->addressCity,
            'AddressStateCode' => $response->addressStateCode,
            'AddressZipCode' => $response->addressZipCode,
            'CountyDistrict' => $response->countyDistrict,
            'showCountyDistrict' => ($response->countyDistrict) ? true : false,
            'Country' => 'USofA', //$response->country, // TODO
            'PhoneWork' => $response->addressPhoneWork,
            'FaxNumber' => $response->addressFaxNumber,
            'IsMainOffice' => $response->isMainOffice,
        ]);
    }

    public static function fromHospiceApi($response): self
    {
        return new self([
            'Id' => $response->id,
            'AgencyId' => $response->agencyId,
            'Name' => $response->agencyName,
            'LocationName' => $response->locationName,
            'AddressLine1' => $response->addressLine1,
            'AddressLine2' => $response->addressLine2,
            'AddressCity' => $response->addressCity,
            'AddressStateCode' => $response->addressStateCode,
            'AddressZipCode' => $response->addressZipCode,
            'CountyDistrict' => $response->countyDistrict,
            'showCountyDistrict' => ($response->countyDistrict) ? true : false,
            'Country' => $response->country,
            'PhoneWork' => $response->addressPhoneWork,
            'FaxNumber' => $response->addressFaxNumber,
            'IsMainOffice' => $response->isMainOffice,
        ]);
    }
}
