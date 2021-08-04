<?php

namespace App\Http\Payloads;

use Spatie\DataTransferObject\DataTransferObjectCollection;

class PatientCollection extends DataTransferObjectCollection
{
    public function __construct(PatientPayload ...$collection)
    {
        $this->collection = $collection;
    }

    public function current(): PatientPayload
    {
        return parent::current();
    }
}
