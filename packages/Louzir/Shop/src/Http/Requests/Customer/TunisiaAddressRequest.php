<?php

namespace Webkul\Shop\Http\Requests\Customer;

use Webkul\Shop\Http\Requests\Concerns\ValidatesTunisianAddress;

class TunisiaAddressRequest extends AddressRequest
{
    use ValidatesTunisianAddress;

    public function rules()
    {
        return array_merge(parent::rules(), $this->tunisianAddressRules($this->all()));
    }
}
