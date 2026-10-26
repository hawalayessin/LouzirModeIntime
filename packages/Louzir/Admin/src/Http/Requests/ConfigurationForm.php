<?php

namespace Webkul\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Webkul\Core\Rules\Decimal;
use Webkul\Core\Rules\PhoneNumber;
use Webkul\Core\Rules\PostCode;
use Webkul\Shop\Helpers\HomeContent;
use Webkul\Shop\Services\RecommendationSettings;

class ConfigurationForm extends FormRequest
{
    /**
     * Determine if the Configuration is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = collect(request()->input('keys', []))->mapWithKeys(function ($item) {
            $data = json_decode($item, true);

            return collect($data['fields'])->mapWithKeys(function ($field) use ($data) {
                $key = "{$data['key']}.{$field['name']}";

                // Check delete key exist in the request
                if (! $this->has("{$key}.delete")) {
                    return [$key => $this->getValidationRules($field['validation'] ?? 'nullable')];
                }

                return [];
            })->toArray();
        })->toArray();

        // Home content rules come from the server, independently of the submitted field metadata.
        foreach (HomeContent::schema() as $group => $fields) {
            foreach ($fields as $name => $field) {
                $key = 'general.louzir_home.'.$group.'.'.$name;

                if (! $this->has($key) && ! $this->hasFile($key)) {
                    continue;
                }

                if ($field['type'] === 'image') {
                    $rules[$key] = $this->has($key.'.delete')
                        ? ['array:delete']
                        : ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'];

                    continue;
                }

                $rules[$key] = ['nullable', 'string', 'max:2000'];

                if ($field['type'] === 'color') {
                    $rules[$key] = ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];
                } elseif ($field['type'] === 'boolean') {
                    $rules[$key] = ['required', 'boolean'];
                } elseif ($field['type'] === 'number') {
                    $rules[$key] = ['required', 'integer', 'min:'.$field['min'], 'max:'.$field['max']];
                } elseif ($field['type'] === 'select') {
                    $rules[$key] = ['required', 'in:'.implode(',', $field['options'])];
                }

                if (in_array($field['type'], ['link', 'map'])) {
                    $rules[$key][] = function ($attribute, $value, $fail) use ($field) {
                        if (! is_string($value) || ! HomeContent::validUrl($value, $field['type'] === 'map')) {
                            $fail(trans('validation.url', ['attribute' => $attribute]));
                        }
                    };
                }
            }
        }

        foreach (['flatrate', 'express'] as $carrier) {
            $prefix = 'sales.carriers.'.$carrier.'.';
            foreach ([
                'active' => ['required', 'boolean'],
                'default_rate' => ['required', 'numeric', 'min:0', 'max:999999.999'],
                'type' => ['required', 'in:per_order,per_unit'],
                'title' => ['required_if:'.$prefix.'active,1', 'nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
            ] as $field => $validation) {
                if ($this->has($prefix.$field)) {
                    $rules[$prefix.$field] = $validation;
                }
            }
        }

        foreach (RecommendationSettings::rules() as $field => $validation) {
            $key = RecommendationSettings::PREFIX.$field;
            if ($this->has($key)) {
                $rules[$key] = $validation;
            }
        }

        if ($this->has('general.content.header_offer.background_color')) {
            $rules['general.content.header_offer.background_color'] = ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];
        }

        foreach ([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'encryption' => ['required', 'in:tls,ssl,none'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:1024'],
        ] as $name => $validation) {
            if ($this->has('emails.configure.smtp.'.$name)) {
                $rules['emails.configure.smtp.'.$name] = $validation;
            }
        }
        foreach (['order_received_message', 'order_confirmed_message', 'newsletter_message'] as $name) {
            if ($this->has('emails.configure.customer_messages.'.$name)) {
                $rules['emails.configure.customer_messages.'.$name] = ['nullable', 'string', 'max:2000'];
            }
        }

        return $rules;
    }

    /**
     * Transform validation rules into an array and map custom validation rules
     *
     * @param  string|array  $validation
     * @return array
     */
    protected function getValidationRules($validation)
    {
        $validations = is_array($validation) ? $validation : explode('|', $validation);

        return array_map(function ($rule) {
            return match ($rule) {
                'phone' => new PhoneNumber,
                'postcode' => new PostCode,
                'decimal' => new Decimal,
                default => $rule,
            };
        }, $validations);
    }
}
