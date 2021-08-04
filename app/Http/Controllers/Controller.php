<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    /**
     * Set message.
     *
     * @param   string   $message
     * @param   integer  $status
     * @param   array    $body
     * @return  boolean
     */
    protected function setMessage($message, $status = 200, $body = [])
    {
        $this->message = $message;
        $this->statusCode = $status;
        $this->body = $body;

        return ($status == 200);
    }

    /**
     * Get message.
     *
     * @return  boolean
     */
    protected function getMessage()
    {
        return $this->message;
    }

    /**
     * Get status code.
     *
     * @return  boolean
     */
    protected function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get request body.
     *
     * @return  array
     */
    protected function getBody()
    {
        return $this->body;
    }

    protected function jsonResponseWithData(string $message = null, $code = 201)
    {
        return response()->json(
            [
                "message" => $message ? $message : "User Created Successfully"
            ],
            $code
        );
    }

    protected function jsonResponseWithError(string $error)
    {
        return response()->json([
            'status' => 'fail',
            'error' => $error,
        ], 422);
    }
}
