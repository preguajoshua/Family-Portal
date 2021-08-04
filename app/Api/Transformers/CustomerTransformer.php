<?php

namespace App\Api\Transformers;

use App\Api\Transformers\SourceTransformer;
use Illuminate\Database\Eloquent\Collection;

class CustomerTransformer extends TransformerAbstract
{
    public function transform($data)
    {
        $sources = new Collection($data->get('cardDetails'));

        return [
            'id' => $data->get('accountId'),
            'first_name' => $data->get('firstName'),
            'last_name' => $data->get('lastName'),
            'default_source' => $data->get('defaultCardId'),
            'sources' => (new SourceTransformer)->collection($sources),
        ];
    }
}
