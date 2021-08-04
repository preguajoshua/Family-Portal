<?php
namespace App\Api\Transformers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class TransformerAbstract implements TransformerInterface
{
    public function collection(Collection $items)
    {
        return $items->transform(array($this, '_transform'));
    }

    public function _transform($data)
    {
        if ($data instanceof Model) {
            return $this->transform(new ParameterBag($data->toArray()));
        }
        return $this->transform(new ParameterBag((array) $data));
    }
}
