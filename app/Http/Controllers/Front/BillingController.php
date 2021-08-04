<?php

namespace App\Http\Controllers\Front;

use Auth;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\HomeCareApi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Api\Transformers\SourceTransformer;
use App\Api\Transformers\CustomerTransformer;
use App\Exceptions\HomeCareApiRequestException;
use App\Exceptions\HomeCareApiNotFoundException;
use App\Exceptions\HomeCareApiAccountExistsException;

class BillingController extends Controller
{
    /**
     * The HomeCare API service.
     *
     * @var  \App\Services\HomeCareApi
     */
    protected $homeCareApi;

    /**
     * Instantiate the billing controller.
     *
     * @param  \App\Services\HomeCareApi  $homeCareApi
     */
    public function __construct(HomeCareApi $homeCareApi)
    {
        $this->homeCareApi = $homeCareApi;
    }

    /**
     * Payment gateway status.
     *
     * @return  Response
     */
    public function paymentGatewayStatus()
    {
        $paymentGatewayStatus = config('axxess.payment_gateway');

        return payload(
            ['status' => $paymentGatewayStatus]
        );
    }

    /**
     * Get a client token.
     *
     * @return  string|Response
     */
    public function clientToken()
    {
        $user = Auth::getUser();

        try {
            $customerToken = $this->homeCareApi->getPaymentToken($user);

        } catch (HomeCareApiRequestException $e) {
            return error($e->getMessage());

        } catch (HomeCareApiNotFoundException $e) {
            try {
                $customerId = $this->createUserPaymentProfile($user);

            } catch (Exception $e) {
                return error($e->getMessage());
            }

            $user->setCustomerId($customerId);
            return $this->clientToken();
        }

        return $customerToken;
    }

    /**
     * Create user payment profile.
     *
     * @param   \App\Models\User  $user
     * @return  string
     *
     * @throws  Exception
     */
    private function createUserPaymentProfile(User $user)
    {
        try {
            $customerId = $this->homeCareApi->createPaymentProfile($user);

        } catch (HomeCareApiRequestException | HomeCareApiAccountExistsException $e) {
            throw new Exception($e->getMessage());
        }

        return $customerId;
    }

    /**
     * Get the customers details and credit cards.
     *
     * @return  Response
     */
    public function sources()
    {
        $user = Auth::getUser();

        try {
            $customerProfile = $this->homeCareApi->getPaymentProfile($user);

        } catch (HomeCareApiRequestException $e) {
            Log::channel('teams')->error($e->getMessage());

            $user->resetCustomerId();
            return payload();

        } catch (HomeCareApiNotFoundException $e) {
            try {
                $customerId = $this->createUserPaymentProfile($user);

            } catch (Exception $e) {
                return error($e->getMessage());
            }

            $user->setCustomerId($customerId);
            return $this->sources();
        }

        $data = (new CustomerTransformer)->_transform($customerProfile);

        return payload($data);
    }

    /**
     * Set the customers dedault credit card.
     *
     * @param   string  $customerId
     * @param   \Illuminate\Http\Request  $request
     * @return  Response
     */
    public function updateCustomer($customerId, Request $request)
    {
        $user = Auth::getUser();
        $defaultCardId = $request->get('default_source');

        try {
            $this->homeCareApi->setDefaultPaymentCard($user, $defaultCardId);

        } catch (HomeCareApiRequestException | HomeCareApiNotFoundException $e) {
            return error($e->getMessage());
        }

        return payload([
            'id' => $customerId,
            'default_source' => $defaultCardId,
        ]);
    }

    /**
     * Add a credit card to the customer.
     *
     * @param   \Illuminate\Http\Request  $request
     * @return  Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'billing_first_name' => 'required',
            'billing_last_name' => 'required',
            'billing_address_line_1' => 'required',
            'billing_address_city' => 'required',
            'billing_address_state' => 'required',
            'billing_address_zipcode' => 'required',
            'billing_phone' => 'required',
            'payment_method_token' => 'required',
            'card_first_name' => 'required',
            'card_last_name' => 'required',
        ]);

        $user = Auth::getUser();

        try {
            $card = $this->homeCareApi->addPaymentCard($user, $validatedData);

        } catch (HomeCareApiRequestException $e) {
            $user->resetCustomerId();
            return error($e->getMessage());

        } catch (HomeCareApiNotFoundException $e) {
            try {
                $customerId = $this->createUserPaymentProfile($user, [
                    'firstName' => $validatedData['billing_first_name'],
                    'lastName' => $validatedData['billing_last_name'],
                ]);

            } catch (Exception $e) {
                return error($e->getMessage());
            }

            $user->setCustomerId($customerId);
            return $this->store($request);
        }

        $data = (new SourceTransformer)->_transform($card);

        return payload($data);
    }

    /**
     * Remove a credit card from the customer.
     *
     * @param   string  $cardId
     * @return  Response
     */
    public function destroy($cardId)
    {
        $user = Auth::getUser();

        try {
            $this->homeCareApi->removePaymentCard($user, $cardId);

        } catch (HomeCareApiRequestException | HomeCareApiNotFoundException $e) {
            return error($e->getMessage());
        }

        return payload([
            'id' => $cardId,
            'deleted' => true,
        ]);
    }
}
