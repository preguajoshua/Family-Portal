<?php

namespace App\Http\Payloads;

use Spatie\DataTransferObject\DataTransferObjectCollection;

class ContactCollection extends DataTransferObjectCollection
{
    public function __construct(ContactPayload ...$collection)
    {
        $this->collection = $collection;
    }

    public function current(): ContactPayload
    {
        return parent::current();
    }
}
