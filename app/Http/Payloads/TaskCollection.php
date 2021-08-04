<?php

namespace App\Http\Payloads;

use Spatie\DataTransferObject\DataTransferObjectCollection;

class TaskCollection extends DataTransferObjectCollection
{
    protected $totalCount;

    public function __construct($totalCount = 0, TaskPayload ...$collection)
    {
        $this->totalCount = $totalCount;
        $this->collection = $collection;
    }

    public function nonPaginatedCount()
    {
        return $this->totalCount;
    }

    public function current(): TaskPayload
    {
        return parent::current();
    }
}
