<?php

namespace App\Fakes;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Facades\Cache;

class HomeCareApiFake
{
    //
    public function patients(Request $request)
    {
        $patientContactIds = $request->query('PatientContactIds');

        if (!is_array($patientContactIds)) {
            $patientContactIds = (array) $patientContactIds;
        }

        $data = [];

        foreach ($patientContactIds as $n => $patientContactId) {
            $patientId = Patient::where('emr_patient_contact_id', $patientContactId)->value('emr_patient_id');

            $data[] = [
                'id' => $patientId ?? '00000000-0000-0000-0000-000000000000',
                'patientContactId' => $patientContactId,
                'agencyId' => '00000000-0000-0000-0000-000000000001',
                'agencyLocationId' => '00000000-0000-0000-0000-000000000002',
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
        $locationId = $request->query('LocationId');

        $data = [
            'agencyLocation' => [
                'id' => $locationId,
                'agencyId' => '00000000-0000-0000-0000-000000000001',
                'addressLine1' => '101 Main Street',
                'addressLine2' => '',
                'addressCity' => 'Dallas',
                'addressStateCode' => 'TX',
                'addressZipCode' => '75000',
                'countyDistrict' => null,
                'country' => 229,
                'addressPhoneWork' => '5551112222',
                'addressFaxNumber' => '5551113333',
                'locationName' => 'Dallas',
                'agencyName' => 'ACME Testing Agency',
                'isMainOffice' => true
            ],
        ];

        return response()->json($data, $status = 200);
    }

    //
    public function patientTasks(Request $request)
    {
        $patientId = $request->query('PatientId');
        $patientContactId = $request->query('PatientContactId');
        $startDate = $request->query('StartDate');
        $endDate = $request->query('EndDate');
        $page = $request->query('Page');
        $pageLength = $request->query('PageLength');

        $tasks = Cache::remember('tasks', now()->addMinutes(30), function () {
            return $this->generateTasks();
        });

        $data = [
            'itemCount' => count($tasks),
            'pageLength' => $pageLength,
            'currentPage' => $page,
            'pageCount' => ceil(count($tasks) / $pageLength),
            'items' => collect($tasks)->forPage($page, $pageLength)->values()->all(),
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Generate some tasks.
     *
     * @param   integer  $count
     * @return  array
     */
    private function generateTasks($count = 50)
    {
        $tasks = [];

        $words = Pluralizer::$uncountable;

        foreach (range(1, $count) as $n) {
            $date = today()->subDays($n)->format('Y-m-d');
            shuffle($words);

            $tasks[] = [
                'id' => sprintf('00000000-0000-0000-0000-%s', str_pad($n, 12, 0, STR_PAD_LEFT)),
                'eventStartTime' => "{$date}T00:01:00",
                'eventEndTime' => "{$date}T00:02:00",
                'taskName' => 'Skilled Nurse Visit',
                'visitStartTime' => "{$date}T00:01:05",
                'visitEndTime' => "{$date}T00:02:05",
                'status' => 420,
                'documentId' =>  $n % 2 === 0 ? 400 : 0,
                'isAllDay' => false,
                'isMissedVisit' => false,
                'isCompleted' => $n % 2 === 0,
                'userFirstName' => 'Nurse',
                'userLastName' => ucfirst($words[0]),
                'userPhotoId' => '00000000-0000-0000-0000-000000000000',
            ];
        }

        return $tasks;
    }

    //
    public function patientMedications(Request $request)
    {
        $patientId = $request->query('PatientId');
        $patientContactId = $request->query('PatientContactId');
        $page = $request->query('Page');
        $pageLength = $request->query('PageLength');

        $today = today()->format('Y-m-d');

        $medications = [];
        $medications[] = [
            'id' => '00000000-0000-0000-0000-000000000001',
            'medicationDosage' => 'LISINOPRIL 10 MG ORAL TABLET',
            'lastChangedDate' => '2019-12-31T00:00:00',
            'classification' => 'cardiovascular agents',
            'frequency' => 'Daily',
            'route' => 'By mouth (PO)',
            'medicationCategory' => 2,
            'active' => false,
            'physicianFirstName' => 'Jonathan',
            'physicianLastName' => 'Knutson',
            'pharmacyName' => 'CVS',
        ];
        $medications[] = [
            'id' => '00000000-0000-0000-0000-000000000002',
            'medicationDosage' => 'ASPIR 81',
            'lastChangedDate' => '2019-12-31T00:00:00',
            'classification' => 'analgesics',
            'frequency' => 'Daily',
            'route' => 'By mouth (PO)',
            'medicationCategory' => 2,
            'active' => true,
            'physicianFirstName' => 'Kay',
            'physicianLastName' => 'Gregory',
            'pharmacyName' => 'CVS',
        ];

        $data = [
            'itemCount' => count($medications),
            'pageLength' => $pageLength,
            'currentPage' => $page,
            'pageCount' => ceil(count($medications) / $pageLength),
            'items' => $medications,
        ];

        return response()->json($data, $status = 200);
    }

    //
    public function patientContacts(Request $request)
    {
        $patientId = $request->query('PatientId');
        $patientContactId = $request->query('PatientContactId');

        $contacts = [];
        $contacts[] = [
            'id' => '00000000-0000-0000-0000-000000000001',
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'phoneHome' => '5551112222',
            'phoneMobile' => '',
            'emailAddress' => 'jane@example.com',
            'relationship' => 'G8',
            'isPayor' => true,
            'isPrimary' => true,
        ];
        $contacts[] = [
            'id' => '00000000-0000-0000-0000-000000000002',
            'firstName' => 'James',
            'lastName' => 'Doe',
            'phoneHome' => '5552223333',
            'phoneMobile' => '',
            'emailAddress' => 'james@example.com',
            'relationship' => 'G8',
            'isPayor' => false,
            'isPrimary' => false,
        ];

        $data = [
            'patientContacts' => $contacts,
        ];

        return response()->json($data, $status = 200);
    }

    //
    public function patientPhysicians(Request $request)
    {
        $patientId = $request->query('PatientId');
        $patientContactId = $request->query('PatientContactId');

        $physicians = [];
        $physicians[] = [
            'id' => '00000000-0000-0000-0000-000000000001',
            'firstName' => 'Dave',
            'lastName' => 'Pepper',
            'phoneWork' => '5551112222',
            'emailAddress' => null,
            'isPrimary' => true,
            'specialty' => 'Dermatology',
        ];
        $physicians[] = [
            'id' => '00000000-0000-0000-0000-000000000002',
            'firstName' => 'Frank',
            'lastName' => 'Frankenstein',
            'phoneWork' => '5551112222',
            'emailAddress' => null,
            'isPrimary' => false,
            'specialty' => 'Experimental',
        ];

        $data = [
            'patientPhysicians' => $physicians,
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Get the user permissions.
     *
     * @param   string  $agencyId
     * @param   string  $patientContactId
     * @param   string  $patientId
     * @return  Response
     */
    public function permissions($agencyId, $patientContactId, $patientId)
    {
        $data = [
            'familyPortalUserPermission' => [
                'agencyId' => $agencyId,
                'familyPortalUserId' => $patientContactId,
                'patientId' => $patientId,
                'isPayor' => true,
                'isAgencyBankAccountSetup' => true,
                'isPatientAccountSetup' => true,
                'familyViewDocumentationAccess' => true,
            ],
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Create a payment profile.
     *
     * @return  Response
     */
    public function createPaymentProfile()
    {
        $data = [
            'isSuccessful' => true,
            'customerAccountId' => '00000000-0000-0000-0000-000000000000',
        ];

        return response()->json($data, $status = 201);
    }

    /**
     * Get payment profile.
     *
     * @param   string  $patientId
     * @return  Response
     */
    public function getPaymentProfile($patientId)
    {
        $data = [
            'accountDetails' => [
                'accountId' => '00000000-0000-0000-0000-000000000000',
                'agencyId' => '00000000-0000-0000-0000-000000000000',
                'patientId' => $patientId,
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john@exmaple.com',
                'defaultCardId' => null,
            ],
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Get payment token.
     *
     * @param   string  $patientId
     * @return  Response
     */
    public function getPaymentToken($patientId)
    {
        $data = [
            'clientToken' => 'eyJ2ZXJzaW9uIjoyLCJlbnZpcm9ubWVudCI6InByb2R1Y3Rpb24iLCJhdXRob3JpemF0aW9uRmluZ2VycHJpbnQiOiJleUowZVhBaU9pSktWMVFpTENKaGJHY2lPaUpGVXpJMU5pSXNJbXRwWkNJNklqSXdNVGd3TkRJMk1UWXRjSEp2WkhWamRHbHZiaUlzSW1semN5STZJbWgwZEhCek9pOHZZWEJwTG1KeVlXbHVkSEpsWldkaGRHVjNZWGt1WTI5dEluMC5leUpsZUhBaU9qRTFPVGM1TnpFeU16WXNJbXAwYVNJNklqSXlaVFkwTW1JMUxXVmhZak10TkdZeE5pMDRNamcwTFRSbE5EUTJaVE5qTVdFMVpTSXNJbk4xWWlJNklqaDBlR28zTTJ0NWRIWm1ZbUpxYmpjaUxDSnBjM01pT2lKb2RIUndjem92TDJGd2FTNWljbUZwYm5SeVpXVm5ZWFJsZDJGNUxtTnZiU0lzSW0xbGNtTm9ZVzUwSWpwN0luQjFZbXhwWTE5cFpDSTZJamgwZUdvM00ydDVkSFptWW1KcWJqY2lMQ0oyWlhKcFpubGZZMkZ5WkY5aWVWOWtaV1poZFd4MElqcG1ZV3h6Wlgwc0luSnBaMmgwY3lJNld5SnRZVzVoWjJWZmRtRjFiSFFpWFN3aWMyTnZjR1VpT2xzaVFuSmhhVzUwY21WbE9sWmhkV3gwSWwwc0ltOXdkR2x2Ym5NaU9uc2lZM1Z6ZEc5dFpYSmZhV1FpT2lJMk5EWTNPRGd3TmpjMEluMTkuN1pINV9HcHh5Qkk4QmdJeHRuM29ndFRtVG9vVHJveFhzTndUdjR6VGFibExzRmZ1emxaQ0x6UjBicXhSNTB6Szh4TlhqTDdjSmxxb0R4cWpSM3Aya2c/Y3VzdG9tZXJfaWQ9IiwiY29uZmlnVXJsIjoiaHR0cHM6Ly9hcGkuYnJhaW50cmVlZ2F0ZXdheS5jb206NDQzL21lcmNoYW50cy84dHhqNzNreXR2ZmJiam43L2NsaWVudF9hcGkvdjEvY29uZmlndXJhdGlvbiIsImhhc0N1c3RvbWVyIjp0cnVlLCJncmFwaFFMIjp7InVybCI6Imh0dHBzOi8vcGF5bWVudHMuYnJhaW50cmVlLWFwaS5jb20vZ3JhcGhxbCIsImRhdGUiOiIyMDE4LTA1LTA4In0sImNoYWxsZW5nZXMiOltdLCJjbGllbnRBcGlVcmwiOiJodHRwczovL2FwaS5icmFpbnRyZWVnYXRld2F5LmNvbTo0NDMvbWVyY2hhbnRzLzh0eGo3M2t5dHZmYmJqbjcvY2xpZW50X2FwaSIsImFzc2V0c1VybCI6Imh0dHBzOi8vYXNzZXRzLmJyYWludHJlZWdhdGV3YXkuY29tIiwiYXV0aFVybCI6Imh0dHBzOi8vYXV0aC52ZW5tby5jb20iLCJhbmFseXRpY3MiOnsidXJsIjoiaHR0cHM6Ly9jbGllbnQtYW5hbHl0aWNzLmJyYWludHJlZWdhdGV3YXkuY29tLzh0eGo3M2t5dHZmYmJqbjcifSwidGhyZWVEU2VjdXJlRW5hYmxlZCI6ZmFsc2UsInBheXBhbEVuYWJsZWQiOmZhbHNlLCJtZXJjaGFudElkIjoiOHR4ajcza3l0dmZiYmpuNyIsInZlbm1vIjoib2ZmIn0=',
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Get patient credit cards.
     *
     * @param   string  $patientId
     * @return  Response
     */
    public function getPatientCreditCards($patientId)
    {
        $data = [
            'patientCreditCards' => [
                [
                    'id' => '00000000-0000-0000-0000-000000000000',
                    'agencyId' => '00000000-0000-0000-0000-000000000000',
                    'patientId' => $patientId,
                    'cardAccountId' => '00000000-0000-0000-0000-000000000000',
                    'referenceType' => 'card',
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'brand' => 'visa',
                    'last4' => '1111',
                    'cvcCheck' => null,
                    'expiryMonth' => 12,
                    'expiryYear' => 2022,
                    'funding' => null,
                    'phone' => '(555) 123-4567',
                    'billingAddress' => [
                        'line1' => '101 Main Street',
                        'line2' => null,
                        'administrativeArea' => 'TX',
                        'subAdministrativeArea' => null,
                        'locality' => 'Dallas',
                        'postalCode' => '75000',
                        'postalCodeAdditional' => null,
                        'country' => 229,
                    ],
                    'isDefault' => true,
                ],
            ],
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Add a payment card.
     *
     * @return  Response
     */
    public function addPaymentCard()
    {
        $data = [
            'isSuccessful' => true,
            'cardId' => '00000000-0000-0000-0000-000000000000',
        ];

        return response()->json($data, $status = 201);
    }

    /**
     * Get a payment card.
     *
     * @param   string  $cardId
     * @return  Response
     */
    public function getPaymentCard($cardId)
    {
        $data = [
            'cardDetails' => [
                'id' => $cardId,
                'agencyId' => '00000000-0000-0000-0000-000000000000',
                'patientId' => '00000000-0000-0000-0000-000000000000',
                'cardAccountId' => '00000000-0000-0000-0000-000000000000',
                'referenceType' => 'card',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'brand' => 'visa',
                'last4' => '1111',
                'cvcCheck' => null,
                'expiryMonth' => 12,
                'expiryYear' => 2022,
                'funding' => null,
                'phone' => '(555) 123-4567',
                'billingAddress' => [
                    'line1' => '101 Main Street',
                    'line2' => null,
                    'administrativeArea' => 'TX',
                    'subAdministrativeArea' => null,
                    'locality' => 'Dallas',
                    'postalCode' => '75000',
                    'postalCodeAdditional' => null,
                    'country' => 229,
                ],
                'isDefault' => true,
            ],
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Set the default credit card.
     *
     * @param   string  $patientId
     * @param   string  $cardId
     * @return  Response
     */
    public function setDefaultPaymentCard($patientId, $cardId)
    {
        $data = [];

        return response()->json($data, $status = 204);
    }

    /**
     * Update a payment card.
     *
     * @param   string  $cardId
     * @return  Response
     */
    public function updatePaymentCard($cardId)
    {
        $data = [
            // TODO
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Remove a payment card.
     *
     * @param   string  $cardId
     * @return  Response
     */
    public function removePaymentCard($cardId)
    {
        $data = [];

        return response()->json($data, $status = 204);
    }

    /**
     * Charge a users card.
     *
     * @return  Response
     */
    public function charge()
    {
        $data = [
            'isSuccessful' => true,
            'transactionRefId' => '00000000-0000-0000-0000-000000000000',
            'confirmationId' => '12345678',
        ];

        return response()->json($data, $status = 201);
    }

    /**
     * Get an invoice.
     *
     * @param   string  $patientId
     * @param   string  $invoiceId
     * @return  Response
     */
    public function getInvoice($patientId, $invoiceId)
    {
        $data = [
            // TODO
        ];

        return response()->json($data, $status = 200);
    }

    /**
     * Get task documentation.
     *
     * @param   Request $request
     * @return  Response
     */
    public function documentPrint(Request $request)
    {
        $taskId = $request->query('taskId');
        $patientId = $request->query('patientId');
        $patientContactId = $request->query('patientContactId');

        $pathToFile = base_path('tests/assets/fake-document.pdf');

        return response()->download($pathToFile);
    }
}
