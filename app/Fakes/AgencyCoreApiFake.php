<?php

namespace App\Fakes;

use Illuminate\Support\Carbon;

class AgencyCoreApiFake
{
    /**
     * Get the users clients.
     *
     * @param   string  $loginId
     * @return  Response
     */
    public function clients($loginId)
    {
        $data = [
            [
                'Id' => '00000000-0000-0000-0000-000000000001',
                'FirstName' => 'John',
                'LastName' => 'Doe',
                'MiddleInitial' => null,
                'DateOfBirth' => '1970-11-22T00:00:00',
                'Gender' => 'Male',
                'PhotoId' => '00000000-0000-0000-0000-000000000000',
                'AgencyId' => '00000000-0000-0000-0000-000000000000',
                'AgencyLocationId' => '00000000-0000-0000-0000-000000000000',
                'PhoneHome' => '5551231111',
                'PhoneMobile' => '',
                'EmailAddress' => null,
                'StartOfCareDate' => '2020-01-01T00:00:00',
                'ContactId' => '00000000-0000-0000-0000-000000000000'
            ],
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Get the agencies location details.
     *
     * @param   string  $locationId
     * @return  Response
     */
    public function location($locationId)
    {
        $data = [
            'Id' => $locationId,
            'AgencyId' => '00000000-0000-0000-0000-000000000000',
            'AddressLine1' => '101 Main Street',
            'AddressCity' => 'Dallas',
            'AddressStateCode' => 'TX',
            'AddressZipCode' => '75000',
            'PhoneWork' => '5551232222',
            'FaxNumber' => '5551233333',
            'IsMainOffice' => true,
            'LocationName' => 'Main',
            'Country' => 229,
            'CountyDistrict' => '',
            'Name' => 'ACME Home Health Care'
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Get the patients contacts.
     *
     * @param   string  $patientId
     * @return  Response
     */
    public function PatientContacts($patientId)
    {
        $data = [
            [
                'Id' => '00000000-0000-0000-0000-000000000001',
                'FirstName' => 'Jane',
                'LastName' => 'Doe ',
                'PhoneHome' => '5551234444',
                'PhoneMobile' => '',
                'EmailAddress' => null,
                'Relationship' => 'spouse',
                'IsPrimary' => true,
            ],
            [
                'Id' => '00000000-0000-0000-0000-000000000002',
                'FirstName' => 'James',
                'LastName' => 'Doe ',
                'PhoneHome' => '5551235555',
                'PhoneMobile' => '',
                'EmailAddress' => null,
                'Relationship' => 'father',
                'IsPrimary' => false,
            ],
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Get the patients medications.
     *
     * @param   string  $patientId
     * @return  Response
     */
    public function patientMedications($patientId)
    {
        $data = [
            [
                'Id' => '00000000-0000-0000-0000-000000000001',
                'StartDate' => '2020-01-01T00:00:00',
                'IsLongStanding' => true,
                'MedicationDosage' => 'ACYCLOVIR 200 MG ORAL CAPSULE',
                'Route' => 'By mouth (PO)',
                'Frequency' => '2x daily',
                'MedicationType' => [
                    'Text' => 'New',
                    'Value' => 'N'
                ],
                'Classification' => 'anti-infectives',
                'MedicationCategory' => 'DC',
                'DCDate' => '2020-01-01T00:00:00',
                'LastChangedDate' => '2020-01-01T00:00:00',
                'LexiDrugId' => 'd00001',
            ],
            [
                'Id' => '00000000-0000-0000-0000-000000000002',
                'StartDate' => '2018-10-31T00:00:00',
                'IsLongStanding' => false,
                'MedicationDosage' => 'HALDOL 2 MG ORAL TABLET',
                'Route' => 'By mouth (PO)',
                'Frequency' => 'Every 6 hours as needed (agitation)',
                'MedicationType' => [
                    'Text' => 'New',
                    'Value' => 'N'
                ],
                'Classification' => 'miscellaneous antipsychotic agents',
                'MedicationCategory' => 'Active',
                'DCDate' => '2020-01-01T00:00:00',
                'LastChangedDate' => '2020-01-01T00:00:00',
                'LexiDrugId' => 'd00027',
            ],
            [
                'Id' => '00000000-0000-0000-0000-000000000003',
                'StartDate' => '2019-10-31T00:00:00',
                'IsLongStanding' => false,
                'MedicationDosage' => 'NOVOLOG',
                'Route' => 'subcutaneous (SQ)',
                'Frequency' => '3x daily before meals.\n<150 mg/dl: 0 units\n150-180 mg/dl: 2 units\n181-200 mg/dl: 3 units\n201-250 mg/dl: 4 units\n251-300 mg/dl: 5 units\n> 300 mg/dl: 7 units',
                'MedicationType' => [
                  'Text' => 'New',
                  'Value' => 'N'
                ],
                'Classification' => 'antidiabetic agents',
                'MedicationCategory' => 'Active',
                'DCDate' => '2019-01-01T00:00:00',
                'LastChangedDate' => '2019-01-01T00:00:00',
                'LexiDrugId' => 'd04697',
            ],
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Get the patients physicians.
     *
     * @param   string  $patientId
     * @return  Response
     */
    public function patientPhysicians($patientId)
    {
        $data = [
            [
                'Id' => '00000000-0000-0000-0000-000000000001',
                'FirstName' => 'Max',
                'LastName' => 'Power',
                'PhoneWork' => '5551236666',
                'EmailAddress' => null,
                'Specialty' => 0,
                'PatientId' => $patientId,
                'IsPrimary' => true
            ],
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Get the patients tasks.
     *
     * @param   string  $patientId
     * @return  Response
     */
    public function patientTasks($patientId)
    {
        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $data = [
            [
                'Id' => '00000000-0000-0000-0000-000000000001',
                'EpisodeId' => '00000000-0000-0000-0000-000000000000',
                'Discipline' => 'PT',
                'DisciplineTask' => 44,
                'TaskName' => 'PT Evaluation',
                'EventStartDate' => "{$yesterday}T00:00:00",
                'EventEndDate' => "{$yesterday}T00:00:00",
                'TimeIn' => "{$yesterday}T00:00:00",
                'TimeOut' => "{$yesterday}T00:00:00",
                'VisitStartTime' => "{$yesterday}T00:00:00",
                'VisitEndTime' => "{$yesterday}T00:00:00",
                'Status' => 415,
                'DocumentId' => '',
                'IsAllDay' => 0,
                'IsMissedVisit' => false,
                'LastName' => 'Florence',
                'FirstName' => 'Nightingale',
                'UserPhotoId' => null
            ],
            [
                'Id' => '00000000-0000-0000-0000-000000000002',
                'EpisodeId' => '00000000-0000-0000-0000-000000000000',
                'Discipline' => 'Nursing',
                'DisciplineTask' => 89,
                'TaskName' => 'Non-OASIS Start of Care',
                'EventStartDate' => "{$today}T00:00:00",
                'EventEndDate' => "{$today}T00:00:00",
                'TimeIn' => "{$today}T00:00:00",
                'TimeOut' => "{$today}T00:00:00",
                'VisitStartTime' => "{$today}T00:00:00",
                'VisitEndTime' => "{$today}T00:00:00",
                'Status' => 210,
                'DocumentId' => '',
                'IsAllDay' => 0,
                'IsMissedVisit' => false,
                'LastName' => 'Anne',
                'FirstName' => 'Other',
                'UserPhotoId' => null
            ],
        ];

        return response()->json($data, $status = 200);
    }
}
