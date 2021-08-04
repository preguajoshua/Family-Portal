<?php

namespace App\Http\Payloads;

use Illuminate\Support\Str;
use Spatie\DataTransferObject\DataTransferObject;

class TaskPayload extends DataTransferObject
{
    public string $Id;

    public string $TaskName;

    public string $EventStartDate;

    public string $EventEndDate;

    public string $EventStartTime;

    public string $EventEndTime;

    public string $TimeIn;

    public string $TimeOut;

    public string $VisitStartTime;

    public string $VisitEndTime;

    public int $Status;

    public bool $IsAllDay;

    public bool $IsMissedVisit;

    public string $FirstName;

    public string $LastName;

    public bool $CompletedStatus;

    public string $CareGiverName;

    public bool $isEvv;

    public bool $HasActivityTasks;

    public ?string $UserPhotoId;

    public static function fromHomeHealthApi($response): self
    {
        return new self([
            'Id' => $response->Id,
            'TaskName' => $response->TaskName,
            'EventStartDate' => $response->EventStartDate,
            'EventEndDate' => $response->EventEndDate,
            'EventStartTime' => $response->EventStartDate,
            'EventEndTime' => $response->EventEndDate,
            'TimeIn' => $response->TimeIn,
            'TimeOut' => $response->TimeOut,
            'VisitStartTime' => $response->VisitStartTime,
            'VisitEndTime' => $response->VisitEndTime,
            'Status' => $response->Status,
            'IsAllDay' => ($response->IsAllDay === 1) ? true : false,
            'IsMissedVisit' => $response->IsMissedVisit,
            'FirstName' => $response->FirstName,
            'LastName' => $response->LastName,
            'CompletedStatus' => false, // TODO
            'CareGiverName' => "{$response->FirstName} {$response->LastName}",
            'isEvv' => false, // TODO
            'HasActivityTasks' => ($response->TimeIn || $response->TimeOut) ? true : false,
            'UserPhotoId' => $response->UserPhotoId,
        ]);
    }

    public static function fromHomeCareApi($response): self
    {
        return new self([
            'Id' => $response->id,
            'TaskName' => $response->taskName,
            'EventStartDate' => $response->eventStartTime,
            'EventEndDate' => $response->eventEndTime,
            'EventStartTime' => $response->eventStartTime,
            'EventEndTime' => $response->eventEndTime,
            'TimeIn' => $response->visitStartTime,
            'TimeOut' => $response->visitEndTime,
            'VisitStartTime' => $response->visitStartTime,
            'VisitEndTime' => $response->visitEndTime,
            'Status' => $response->status,
            'IsAllDay' => $response->isAllDay,
            'IsMissedVisit' => $response->isMissedVisit,
            'FirstName' => $response->userFirstName,
            'LastName' => $response->userLastName,
            'CompletedStatus' => $response->isCompleted,
            'CareGiverName' => "{$response->userFirstName} {$response->userLastName}",
            'isEvv' => ($response->documentId === 400) ? true : false, // TODO - check this
            'HasActivityTasks' => ($response->visitStartTime || $response->visitEndTime) ? true : false,
            'UserPhotoId' => $response->userPhotoId,
        ]);
    }

    public static function fromHospiceApi($response): self
    {
        $endTime = Str::after($response->visitEndTime, 'T');
        $endDate = Str::before($response->eventEndDate, 'T');
        $startTime = Str::after($response->visitStartTime, 'T');
        $startDate = Str::before($response->eventStartDate, 'T');

        $endDateAndTime = "{$endDate}T{$endTime}";
        $startDateAndTime = "{$startDate}T{$startTime}";

        return new self([
            'Id' => $response->id,
            'TaskName' => $response->taskName,
            'EventStartDate' => $startDateAndTime,
            'EventEndDate' => $endDateAndTime,
            'EventStartTime' => $response->eventStartDate,
            'EventEndTime' => $response->eventEndDate,
            'TimeIn' => $response->visitStartTime,
            'TimeOut' => $response->visitEndTime,
            'VisitStartTime' => $startDateAndTime,
            'VisitEndTime' => $endDateAndTime,
            'Status' => 0,
            'IsAllDay' => true,
            'IsMissedVisit' => $response->isMissedVisit,
            'FirstName' => $response->userFirstName,
            'LastName' => $response->userLastName,
            'CompletedStatus' => $response->isComplete,
            'CareGiverName' => "{$response->userFirstName} {$response->userLastName}",
            'isEvv' => false,
            'HasActivityTasks' => ($response->visitStartTime || $response->visitEndTime) ? true : false,
            'UserPhotoId' => null,
        ]);
    }
}
