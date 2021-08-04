<?php

namespace App\Http\Payloads;

use Spatie\DataTransferObject\DataTransferObjectCollection;

class MedicationCollection extends DataTransferObjectCollection
{
    public function __construct(MedicationPayload ...$collection)
    {
        $this->collection = $collection;
    }

    public function current(): MedicationPayload
    {
        return parent::current();
    }
}
