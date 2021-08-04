<?php

namespace App\Rules;

use App\Models\Emr;
use Illuminate\Contracts\Validation\Rule;

class EmrExists implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Emr::where('application_id', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The application id is not registered.';
    }
}
