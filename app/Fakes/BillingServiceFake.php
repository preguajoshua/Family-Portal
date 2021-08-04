<?php

namespace App\Fakes;

use Illuminate\Http\Request;

class BillingServiceFake
{
    /**
     * An array of invoice objects.
     *
     * @param   Request  $request
     * @return  Response
     */
    public function invoices(Request $request)
    {
        $invoice1 = [
            'Payments' => null,
            'IsUserCanAdd' => false,
            'IsUserCanEdit' => false,
            'IsUserCanPrint' => false,
            'IsUserCanDelete' => false,
            'IsUserCanGenerate' => false,
            'Id' => '00000000-0000-0000-0000-000000000000',
            'AgencyId' => '00000000-0000-0000-0000-000000000000',
            'PatientId' => $request->patientId,
            'StartDate' => '2019-12-31T00:00:00',
            'EndDate' => '2019-12-31T00:00:00',
            'FirstName' => 'John',
            'LastName' => 'Doe',
            'PatientIdNumber' => null,
            'MiddleInitial' => null,
            'EmailAddress' => '',
            'PrimaryInsuranceId' => '00000000-0000-0000-0000-000000000000',
            'PayorName' => null,
            'InsuranceIdNumber' => '',
            'InvoiceNumber' => 1234,
            'InvoiceType' => 4,
            'InvoiceTypeName' => 'Invoice',
            'Status' => 3000,
            'StatusName' => 'Sent',
            'PaymentDate' => '2019-12-31T00:00:00',
            'DueDate' => '2019-12-31T00:00:00',
            'ClaimDate' => '2019-12-31T00:00:00',
            'ProspectivePay' => 0.000,
            'PaidAmount' => 0.000,
            'AdjustmentAmount' => 0.000,
            'NegativeAdjustmentAmount' => 0.000,
            'TotalTax' => 0.000,
            'IndianaOverheadCharges' => 0.0,
            'NetDue' => 99.000,
            'Balance' => 0.000,
            'IsValidEmailExist' => false,
            'DateRange' => '12/22/2019 - 12/28/2019',
            'DisplayName' => 'Doe, John',
            'IsInvoiceSent' => true,
            'IsVisitVerified' => true,
            'IsInfoVerified' => true,
            'IsSupplyVerified' => false,
            'IsFirstBillableVisit' => false,
            'IsVerified' => false,
            'IsVisible' => true,
            'Service' => 0,
            'HealthPlanId' => null,
            'AuthorizationNumber' => null,
            'RemitId' => '00000000-0000-0000-0000-000000000000',
            'EftNumber' => null,
            'InvoiceHasPostPaymentEnabled' => false,
        ];

        return response()->json([$invoice1]);
    }

    /**
     * An invoice object.
     *
     * @param   Request  $request
     * @return  Response
     */
    public function invoice(Request $request)
    {
        $visit1 = [
            'Id' => '00000000-0000-0000-0000-000000000000',
            'ShiftDataId' => '00000000-0000-0000-0000-000000000000',
            'ClaimTaskId' => '00000000-0000-0000-0000-000000000000',
            'ChildId' => 0,
            'DisciplineTask' => 45,
            'Discipline' => 2,
            'Status' => 419,
            'CustomNoteId' => '00000000-0000-0000-0000-000000000000',
            'TaskName' => 'PT Visit',
            'EventStartTime' => '2019-12-31T00:00:00-06:00',
            'IsSupplyExist' => false,
            'ClinicianProviderId' => null,
            'OtherProviderId' => null,
            'UserLastName' => null,
            'UserFirstName' => null,
            'DxPtr' => null,
            'Modifier' => null,
            'Modifier2' => null,
            'Modifier3' => null,
            'Modifier4' => null,
            'HasModifierChanged' => false,
            'HasTravel' => false,
            'TravelTime' => null,
            'ClaimStartDate' => '2019-12-31T00:00:00',
            'ClaimEndDate' => '2019-12-31T00:00:00',
            'SubLineItems' => null,
            'StatusName' => null,
            'IsBillable' => false,
            'PatientId' => '00000000-0000-0000-0000-000000000000',
            'PayorId' => '00000000-0000-0000-0000-000000000000',
            'EventEndTime' => '2019-12-31T00:00:00',
            'IsAllDay' => false,
            'IsMissedVisit' => false,
            'DisplayName' => null,
            'BillableHours' => 0.0,
            'OpenHours' => 0.0,
            'MissedHours' => 0.0,
            'Tags' => null,
            'HasVisitUnitChanged' => false,
            'IsParent' => false,
            'VisitChanges' => null,
            'PreferredName' => null,
            'VisitStartTime' => '2019-12-31T00:00:00-06:00',
            'VisitEndTime' => '2019-12-31T00:00:00-06:00',
            'Unit' => 10.0,
            'Rate' => 100.0,
            'UserId' => '00000000-0000-0000-0000-000000000000',
            'UserDeprecated' => null,
            'TotalCharge' => 0.0,
            'Comments' => null,
            'IsInvoiceCommentsRequired' => false,
            'UserName' => 'Vishwanath Upadhayay RN'
        ];

        $billingAddress = [
            'PrivatePayorId' => '00000000-0000-0000-0000-000000000000',
            'FirstName' => 'John',
            'LastName' => 'Doe',
            'MI' => null,
            'Name' => null,
            'AddressLine1' => '101 Main Street',
            'AddressLine2' => '1st Floor',
            'AddressCity' => 'Dallas',
            'AddressStateCode' => '0',
            'AddressZipCode' => '75000',
            'PhoneHome' => '5551231234',
            'PhoneMobile' => '',
            'FaxNumber' => null,
            'EmailAddress' => null,
            'AddressType' => 1
        ];

        $invoice = [
            'MiddleInitial' => null,
            'IsHomeHealthServiceIncluded' => false,
            'HasMultipleEpisodes' => false,
            'Visits' => [$visit1],
            'CustomLineItems' => [],
            'Service' => 0,
            'IsIndividualPay' => true,
            'PrintContentOnly' => false,
            'HomeHealthTotalCharge' => 0.0,
            'ClaimSubCategory' => 3,
            'Diagnoses' => null,
            'Id' => $request->Id,
            'PayorIdType' => 0,
            'InsuranceIdNumber' => null,
            'InvoiceNumber' => 1234,
            'PatientIdNumber' => '12345',
            'FirstName' => 'John',
            'LastName' => 'Doe',
            'DOB' => '2019-12-31T00:00:00',
            'Gender' => 'Male',
            'AddressLine1' => '101 Main Street',
            'AddressLine2' => '1st Floor',
            'AddressCity' => 'Dallas',
            'AddressStateCode' => '0',
            'AddressZipCode' => '75000',
            'StartDate' => '2019-12-31T00:00:00',
            'EndDate' => '2019-12-31T00:00:00',
            'IsFirstBillableVisit' => false,
            'FirstBillableVisitDate' => '2019-12-31T00:00:00',
            'StartofCareDate' => '2019-12-31T00:00:00',
            'AdmissionHour' => '2019-12-31T00:00:00',
            'AdmissionId' => '00000000-0000-0000-0000-000000000000',
            'DischargeDate' => '2019-12-31T00:00:00',
            'AdmissionSource' => null,
            'AdmissionType' => 0,
            'PatientStatus' => 1,
            'ProspectivePay' => 0.000,
            'AdjustmentAmount' => 0.000,
            'NegativeAdjustmentAmount' => 0.000,
            'PaidAmount' => 0.000,
            'TaxRate' => 0.000,
            'TotalTax' => 0.000,
            'PaymentDate' => '2019-12-31T00:00:00',
            'ClaimDate' => '2019-12-31T00:00:00',
            'DueDate' => '2019-12-31T00:00:00',
            'Status' => 3000,
            'PrimaryInsuranceId' => '00000000-0000-0000-0000-000000000000',
            'InvoiceType' => 4,
            'InvoiceTypeName' => 'Invoice',
            'PayorType' => 10,
            'PrivatePayorId' => '00000000-0000-0000-0000-000000000000',
            'UB4PatientStatus' => null,
            'HealthPlanId' => null,
            'GroupName' => null,
            'GroupId' => null,
            'RelationshipCode' => null,
            'RelationshipDescription' => null,
            'Authorization' => '00000000-0000-0000-0000-000000000000',
            'AuthorizationNumber' => null,
            'AuthorizationNumber2' => null,
            'AuthorizationNumber3' => null,
            'HippsCode' => null,
            'ClaimKey' => null,
            'AssessmentType' => null,
            'CBSA' => null,
            'Comment' => null,
            'Remark' => null,
            'Flags' => 1234,
            'SupplyTotal' => 0.000,
            'SupplyCode' => null,
            'FacilityType' => 0,
            'BillType' => 0,
            'EmailAddress' => null,
            'Modified' => '2019-12-31T00:00:00',
            'Created' => '2019-12-31T00:00:00',
            'BillingAddress' => $billingAddress,
            'DiagnosisCodes' => null,
            'ConditionCodes' => null,
            'Authorizations' => null,
            'AgencyLocationId' => '00000000-0000-0000-0000-000000000000',
            'LocationZipCode' => null,
            'VisitRates' => null,
            'SupplyLineItems' => null,
            'BillVisitDatas' => null,
            'BilledVisitDatas' => null,
            'BillableCompletedNoRateVisits' => null,
            'UnAuthorizatedTasks' => null,
            'ReturnedTasks' => null,
            'Incompletedvisits' => null,
            'BillVisitSummaryDatas' => null,
            'IndianaOverheadVisitGrouping' => null,
            'OverheadCharges' => 0.0,
            'DisplayName' => 'Doe, John',
            'InsuranceBillData' => null,
            'PayorName' => 'Private(Self) Pay',
            'PaymentTerms' => 15,
            'NetDue' => 99.000,
            'IsInvoiceCommentsRequired' => false,
            'EpisodeId' => '00000000-0000-0000-0000-000000000000',
            'PatientId' => $request->patientId,
            'AgencyId' => $request->agencyId,
        ];

        return response()->json($invoice);
    }

    /**
     * A claim payment object.
     *
     * @param   Request  $request
     * @return  Response
     */
    public function claimPayments(Request $request)
    {
        $claim1 = [
            'Id' => '00000000-0000-0000-0000-000000000000',
            'ClaimId' => $request->claimId,
            'Amount' => 0.000,
            'Type' => 1,
            'Payor' => '00000000-0000-0000-0000-000000000000',
            'PayorName' => null,
            'Comments' => null,
            'AdjustmentCode' => null,
            'AdjustmentDescription' => null,
            'TransactionId' => null,
            'CheckRA' => null,
            'CheckAmount' => null,
            'IsNegative' => false,
            'IsDeprecated' => false,
            'Date' => '2019-12-31T00:00:00',
            'Modified' => '2019-12-31T00:00:00',
            'Created' => '2019-12-31T00:00:00',
            'Service' => 0,
            'TypeName' => 'Payment',
            'PatientId' => $request->patientId,
            'AgencyId' => $request->agencyId,
        ];

        return response()->json([$claim1]);
    }

    /**
     * Provide the invoice document for download.
     *
     * @return  Response
     */
    public function downloadInvoice()
    {
        $pathToFile = base_path('tests/assets/fake-invoice.pdf');
        $encodedFileContents = base64_encode(file_get_contents($pathToFile));

        return response($encodedFileContents);
    }

    /**
     * Add electronic payment.
     *
     * @return  Response
     */
    public function addElectronicPayment()
    {
        return response()->json([
            //
        ]);
    }
}
