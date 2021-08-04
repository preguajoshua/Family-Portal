<?php

namespace App\Fakes;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HospiceApiFake
{
    //
    public function patients(Request $request)
    {
        $patientContactIds = $request->query('PatientContactIds');

        if (! is_array($patientContactIds)) {
            $patientContactIds = (array) $patientContactIds;
        }

        $data = [];

        foreach ($patientContactIds as $n => $patientContactId) {
            $patientId = Patient::where('emr_patient_contact_id', $patientContactId)->value('emr_patient_id');

            $data[] = [
                'id' => $patientId ?? '00000000-0000-0000-0000-000000000000',
                'patientContactId' => $patientContactId,
                'agencyId' => '00000000-0000-0000-0000-000000000002',
                'agencyLocationId' => '00000000-0000-0000-0000-000000000003',
                'photoId' => null,
                'firstName' => 'John',
                'lastName' => 'Doe',
                'middleInitial' => null,
                'dateOfBirth' => '1950-12-31',
                'gender' => 'Mail',
                'primaryPhone' => '5551112222',
                'secondaryPhone' => '',
                'emailAddress' => null,
                'startOfCareDate' => '2019-12-31T00:00:00',
            ];
        }

        return response()->json($data, $status = 200);
    }

    //
    public function location(Request $request)
    {
        $agencyId = $request->query('AgencyId');
        $patientId = $request->query('PatientId');
        $patientContactId = $request->query('PatientContactId');

        $data = [
            'errorMessage' => null,
            'status' => 0,
            'id' => '00000000-0000-0000-0000-000000000001',
            'agencyId' => $agencyId,
            'addressLine1' => '101 Main Street',
            'addressLine2' => '',
            'addressCity' => 'Austin',
            'addressStateCode' => 'TX',
            'addressZipCode' => '78000',
            'countyDistrict' => null,
            'country' => 'United States of America',
            'addressPhoneWork' => '5551112222',
            'addressFaxNumber' => '5551113333',
            'locationName' => 'United States',
            'agencyName' => 'Hospice Testing Agency',
            'isMainOffice' => true,
        ];

        return response()->json($data, $status = 200);
    }

    //
    public function patientTasks(Request $request)
    {
        $agencyId = $request->query('AgencyId');
        $patientId = $request->query('PatientId');
        $patientContactId = $request->query('PatientContactId');
        $startDate = $request->query('StartDate');
        $endDate = $request->query('EndDate');
        $page = $request->query('Page');
        $pageLength = $request->query('PageLength');

        $today = today()->format('Y-m-d');

        $tasks = [];
        $tasks[] = [
            'id' => '00000000-0000-0000-0000-000000000001',
            'taskName' => 'Skilled Nurse Visit',
            'eventStartDate' => "{$today}T00:01:00",
            'eventEndDate' => "{$today}T00:02:00",
            'visitStartTime' => "{$today}T00:01:05",
            'visitEndTime' => "{$today}T00:02:05",
            'userFirstName' => 'Florence',
            'userLastName' => 'Nightingale',
            'isMissedVisit' => false,
            'status' => 'Saved',
            'isComplete' => false,
        ];
        $tasks[] = [
            'id' => '00000000-0000-0000-0000-000000000002',
            'taskName' => 'Skilled Nurse Visit',
            'eventStartDate' => "{$today}T00:01:00",
            'eventEndDate' => "{$today}T00:02:00",
            'visitStartTime' => "{$today}T01:01:05",
            'visitEndTime' => "{$today}T02:02:05",
            'userFirstName' => 'Anne',
            'userLastName' => 'Other',
            'isMissedVisit' => false,
            'status' => 'Not Yet Started',
            'isComplete' => false,
        ];

        $data = [
            'errorMessage' => null,
            'status' => 0,
            'itemCount' => count($tasks),
            'pageLength' => $pageLength,
            'currentPage' => $page,
            'pageCount' => ceil(count($tasks) / $pageLength),
            'items' => $tasks,
        ];

        return response()->json($data, $status = 200);
    }

    //
    public function patientMedications(Request $request)
    {
        $agencyId = $request->query('AgencyId');
        $patientId = $request->query('PatientId');
        $patientContactId = $request->query('PatientContactId');
        $page = $request->query('Page');
        $pageLength = $request->query('PageLength');

        $today = today()->format('Y-m-d');

        $medications = [];
        $medications[] = [
            'id' => '00000000-0000-0000-0000-000000000001',
            'medicationName' => 'Lorazepam 0.5 mg oral tablet',
            'frequency' => '3 times daily',
            'medicationDosage' => '1 tab',
            'route' => 'oral',
            'classification' => 'benzodiazepines',
            'active' => false,
            'physicianLastName' => 'Knutson',
            'physicianFirstName' => 'Jonathan',
            'lastChangedDate' => '2019-12-31T00:00:00',
        ];
        $medications[] = [
            'id' => '00000000-0000-0000-0000-000000000002',
            'medicationName' => '12 Hour Nasal',
            'frequency' => '2w1, 3w9',
            'medicationDosage' => '11',
            'route' => 'mouth',
            'classification' => 'topical agents',
            'active' => true,
            'physicianLastName' => 'Gregory',
            'physicianFirstName' => 'Kay',
            'lastChangedDate' => '2019-12-31T00:00:00',
        ];

        $data = [
            'errorMessage' => null,
            'status' => 0,
            'itemCount' => count($medications),
            'pageLength' => $pageLength,
            'currentPage' => $page,
            'pageCount' => ceil(count($medications) / $pageLength),
            'items' => $medications,
        ];

        return response()->json($data, $status = 200);
    }

    //
    public function patientPhysicians(Request $request)
    {
        $agencyId = $request->query('AgencyId');
        $patientId = $request->query('PatientId');
        $patientContactId = $request->query('PatientContactId');

        $data = [];
        $data[] = [
            'id' => '00000000-0000-0000-0000-000000000001',
            'firstName' => 'Dave',
            'lastName' => 'Pepper',
            'isPrimary' => true,
            'phoneWork' => '5551112222',
            'emailAddress' => '',
            'npiNumber' => '1902803224',
            'credentials' => 'M.D.',
        ];
        $data[] = [
            'id' => '00000000-0000-0000-0000-000000000002',
            'firstName' => 'Frank',
            'lastName' => 'Frankenstein',
            'isPrimary' => false,
            'phoneWork' => '5551112222',
            'emailAddress' => '',
            'npiNumber' => '1902803224',
            'credentials' => 'M.D.',
        ];

        return response()->json($data, $status = 200);
    }

    //
    public function patientContacts(Request $request)
    {
        $agencyId = $request->query('AgencyId');
        $patientId = $request->query('PatientId');
        $patientContactId = $request->query('PatientContactId');

        $data = [];
        $data[] = [
            'id' => '00000000-0000-0000-0000-000000000001',
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'isPrimary' => true,
            'relationship' => 'Spouse',
            'phoneHome' => '5551112222',
            'phoneMobile' => null,
            'emailAddress' => 'jane@example.com',
        ];
        $data[] = [
            'id' => '00000000-0000-0000-0000-000000000002',
            'firstName' => 'James',
            'lastName' => 'Doe',
            'isPrimary' => false,
            'relationship' => 'Son',
            'phoneHome' => '5552223333',
            'phoneMobile' => null,
            'emailAddress' => 'james@example.com',
        ];

        return response()->json($data, $status = 200);
    }
}
