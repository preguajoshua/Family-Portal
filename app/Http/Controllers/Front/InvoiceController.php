<?php

namespace App\Http\Controllers\Front;

use Query;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Services\HomeCareApi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Exceptions\HomeCareApiRequestException;
use App\Exceptions\HomeCareApiNotFoundException;
use App\Exceptions\HomeCareApiServiceFeeException;
use App\Facades\Api;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * The HomeCare API service.
     *
     * @var  \App\Services\HomeCareApi
     */
    protected $homeCareApi;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(HomeCareApi $homeCareApi)
    {
        $this->homeCareApi = $homeCareApi;
    }

    /**
     * Show all client invoices.
     *
     * @param   \Illuminate\Http\Request  $request
     * @return  Response
     */
    public function index(Request $request)
    {
        $user = Auth::getUser();

        $patientId = $user->client()->Id;
        $agencyId = $user->client()->AgencyId;
        $unpaid = (int) $request->get('unpaid');

        $invoices = Api::request('BillingService\ClientInvoices', compact('patientId', 'agencyId', 'unpaid'));

        return response()->json($invoices);
    }

    /**
     * Show a clients invoice.
     *
     * @param   string  $claimId
     * @return  Response
     */
    public function show($Id)
    {
        $user = Auth::getUser();

        $patientId = $user->client()->Id;
        $agencyId = $user->client()->AgencyId;

        $invoice = Api::request('BillingService\ClientInvoice', compact('patientId', 'agencyId', 'Id'));

        return response()->json($invoice);
    }

    /**
     * Get clients payment history.
     *
     * @param   string  $claimId
     * @return  Response
     */
    public function payments($claimId)
    {
        $user = Auth::getUser();

        $patientId = $user->client()->Id;
        $agencyId = $user->client()->AgencyId;

        $history = Api::request('BillingService\ClientPayments', compact('patientId', 'agencyId', 'claimId'));

        return response()->json($history);
    }

    /**
     * Process a payment.
     *
     * @param   \Illuminate\Http\Request  $request
     * @param   string   $invoiceId
     * @return  Response
     */
    public function processPayment(Request $request, $invoiceId)
    {
        $user = Auth::getUser();

        if (!$this->processSingleInvoice($user, $invoiceId, $request->get('amount'), $request->get('fee'), $request->get('source'))) {
            return error($this->getMessage(), $this->getStatusCode());
        }

        return response()->json([
            'status' => 'success',
            'message' => $this->getMessage(),
            'codes' => $this->getBody(),
        ]);
    }

    /**
     * Process a mass payment.
     *
     * @param   \Illuminate\Http\Request  $request
     * @return  Response
     */
    public function processMassPayment(Request $request)
    {
        $user = Auth::getUser();

        $patientId = $user->client()->Id;
        $agencyId = $user->client()->AgencyId;
        $unpaid = 1;

        $invoices = Api::request('BillingService\ClientInvoices', compact('patientId', 'agencyId', 'unpaid'));

        $totalAmount = 0;
        $massPayInvoices = [];

        foreach ($invoices as $invoice) {
            if (!in_array($invoice->Id, $request->get('invoices'))) {
                continue;
            }

            $_amount = ($invoice->NetDue - $invoice->PaidAmount + $invoice->AdjustmentAmount) - $invoice->NegativeAdjustmentAmount;

            if ($_amount) {
                $totalAmount += (float) $_amount;
                $massPayInvoices[$invoice->Id] = $_amount;
            }
        }

        $requestAmount = (float) $request->get('amount');

        if ($totalAmount != $requestAmount || !count($massPayInvoices)) {
            return error('Invalid mass payment amount.');
        }

        $fee = $request->get('fee');
        $source = $request->get('source');

        foreach ($massPayInvoices as $invoiceId => $amount) {
            if (!$this->processSingleInvoice($user, $invoiceId, $amount, $fee, $source)) {
                return error($this->getMessage(), $this->getStatusCode());
            }
        }

        return success($this->getMessage(), $this->getStatusCode());
    }

    /**
     * Process a single invoice.
     *
     * @param   \App\Models\User  $user
     * @param   string  $invoiceId
     * @param   string  $amount
     * @param   string  $fee
     * @param   string  $source
     * @return  Response
     */
    private function processSingleInvoice(User $user, $invoiceId, $amount, $fee, $source)
    {
        if (config('axxess.payment_gateway') === false) {
            return $this->setMessage('Payment Gateway is not available.', 500);
        }

        try {
            $result = $this->homeCareApi->charge($user, [
                'invoice_id' => $invoiceId,
                'amount' => $amount,
                'fee' => $fee,
                'source_id' => $source,
            ]);
        } catch (HomeCareApiRequestException | HomeCareApiServiceFeeException | HomeCareApiNotFoundException $e) {
            Log::channel('teams')->error($e->getMessage());

            return $this->setMessage('Unable To Complete Payment.', 500);
        }

        return $this->setMessage('Your payment has been successfully processed.', 200, $result);
    }

    /**
     * Download an invoice.
     *
     * @param   \Illuminate\Http\Request  $request
     * @param   string   $invoiceId
     * @return  Response
     */
    public function download(Request $request, $invoiceId)
    {
        $user = Auth::getUser();
        $patientId = $user->getPatientId();
        $application = $user->getApplication();
        $cluster = $user->getCluster();

        try {
            $invoice = Query::pagination('PatientInvoiceQuery', compact('invoiceId', 'patientId', 'application', 'cluster'));
        } catch (\Throwable $th) {
            abort(404, "Invoice not Found, Please try again later");
        }

        if (!$invoice) {
            //todo log the failure ??
            abort(404, "Invoice not Found, Please try again later");
        }

        $response = Http::timeout(2)->post(config('axxess.billing_service.base_url') . '/invoice/Stream', [
            'agencyId' => $invoice->AgencyId,
            'patientId' => $invoice->PatientId,
            'claimId' => $invoice->Id,
        ]);

        if (!$response->successful()) {
            //todo log the failure ??
            abort(404, "Invoice not Found, Please try again later");
        }

        $content = base64_decode($response->body());

        $method = $request->get('download')
            ? 'attachment'
            : 'inline';

        $filename = sprintf('%s-%s%s-%s.pdf', $invoice->InvoiceNumber, $invoice->FirstName, $invoice->LastName, $invoice->DueDate);

        return response($content)
            ->header('Content-type', 'application/pdf;charset:utf-8')
            ->header('Content-Length', strlen($content))
            ->header('Content-Disposition', "{$method}; filename={$filename}");
    }
}
