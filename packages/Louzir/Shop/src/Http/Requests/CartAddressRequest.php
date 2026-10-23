<?php

namespace Webkul\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Webkul\Core\Rules\PhoneNumber;
use Webkul\Customer\Rules\VatIdRule;
use Webkul\Shop\Http\Requests\Concerns\ValidatesTunisianAddress;

class CartAddressRequest extends FormRequest
{
    use ValidatesTunisianAddress;

    /**
     * Rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Determine if the product is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $this->rules = [];

        $this->mergeAddressRules('billing');

        if (! $this->input('billing.use_for_shipping')) {
            $this->mergeAddressRules('shipping');
        }

        return $this->rules;
    }

    /**
     * Merge new address rules.
     */
    private function mergeAddressRules(string $addressType): void
    {
        $this->mergeWithRules([
            "{$addressType}.company_name" => ['nullable'],
            "{$addressType}.first_name" => ['required'],
            "{$addressType}.last_name" => ['required'],
            "{$addressType}.email" => ['required'],
            "{$addressType}.address" => ['required', 'array', 'min:1'],
            "{$addressType}.phone" => ['required', new PhoneNumber],
        ]);

        $this->mergeWithRules($this->tunisianAddressRules(
            (array) $this->input($addressType, []), $addressType.'.'
        ));

        if ($addressType == 'billing') {
            $this->mergeWithRules([
                "{$addressType}.vat_id" => [(new VatIdRule)->setCountry($this->input('billing.country'))],
            ]);
        }
    }

    /**
     * Merge additional rules.
     */
    private function mergeWithRules($rules): void
    {
        $this->rules = array_merge($this->rules, $rules);
    }
}
