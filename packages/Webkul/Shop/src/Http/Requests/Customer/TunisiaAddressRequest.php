<?php

namespace Webkul\Shop\Http\Requests\Customer;

use Illuminate\Validation\Rule;
use Webkul\Shop\Helpers\TunisiaMunicipalities;

class TunisiaAddressRequest extends AddressRequest
{
    public function rules()
    {
        $regions = collect(app(TunisiaMunicipalities::class)->all());
        $region = $regions->firstWhere('value', $this->input('state'));
        $localities = collect($region['localities'] ?? []);
        $postcodes = $localities->where('name', $this->input('city'))->pluck('postcode')->all();

        return array_merge(parent::rules(), [
            'country' => ['required', Rule::in(['TN'])],
            'state' => ['required', Rule::in($regions->pluck('value')->all())],
            'city' => ['required', 'string', Rule::in($localities->pluck('name')->all())],
            'postcode' => ['required', 'digits:4', Rule::in($postcodes)],
        ]);
    }
}
