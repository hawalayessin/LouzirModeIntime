<?php

namespace Webkul\Shop\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Webkul\Shop\Helpers\TunisiaMunicipalities;

trait ValidatesTunisianAddress
{
    protected function tunisianAddressRules(array $address, string $prefix = ''): array
    {
        $regions = collect(app(TunisiaMunicipalities::class)->all());
        $region = $regions->firstWhere('value', $address['state'] ?? null);
        $localities = collect($region['localities'] ?? []);
        $postcodes = $localities->where('name', $address['city'] ?? null)->pluck('postcode')->all();

        return [
            $prefix.'country' => ['required', Rule::in(['TN'])],
            $prefix.'state' => ['required', Rule::in($regions->pluck('value')->all())],
            $prefix.'city' => ['required', 'string', Rule::in($localities->pluck('name')->all())],
            $prefix.'postcode' => ['required', 'digits:4', Rule::in($postcodes)],
        ];
    }
}
