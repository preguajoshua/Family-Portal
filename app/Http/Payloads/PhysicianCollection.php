<?php

namespace App\Http\Payloads;

use Spatie\DataTransferObject\DataTransferObjectCollection;

class PhysicianCollection extends DataTransferObjectCollection
{
    public function __construct(PhysicianPayload ...$collection)
    {
        $this->collection = $collection;
    }

    public function current(): PhysicianPayload
    {
        return parent::current();
    }
}
