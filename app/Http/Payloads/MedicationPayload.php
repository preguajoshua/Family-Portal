<?php

namespace App\Http\Payloads;

use Spatie\DataTransferObject\DataTransferObject;

class MedicationPayload extends DataTransferObject
{
    public string $Id;

    public string $MedicationDosage;

    public string $Classification;

    public string $Frequency;

    public string $Route;

    public string $LastChangedDate;

    public bool $Active;

    public static function fromHomeHealthApi($response): self
    {
        return new self([
            'Id' => $response->Id,
            'MedicationDosage' => $response->MedicationDosage,
            'Classification' => $response->Classification,
            'Frequency' => $response->Frequency,
            'Route' => $response->Route,
            'LastChangedDate' => $response->LastChangedDate,
            'Active' => ($response->MedicationCategory === 'Active') ? true : false,
        ]);
    }

    public static function fromHomeCareApi($response): self
    {
        return new self([
            'Id' => $response->id,
            'MedicationDosage' => $response->medicationDosage,
            'Classification' => $response->classification,
            'Frequency' => $response->frequency,
            'Route' => $response->route,
            'LastChangedDate' => $response->lastChangedDate,
            'Active' => $response->active,
        ]);
    }

    public static function fromHospiceApi($response): self
    {
        return new self([
            'Id' => $response->id,
            'MedicationDosage' => $response->medicationName,
            'Classification' => $response->classification,
            'Frequency' => $response->frequency,
            'Route' => $response->route,
            'LastChangedDate' => $response->lastChangedDate,
            'Active' => $response->active,
        ]);
    }
}
