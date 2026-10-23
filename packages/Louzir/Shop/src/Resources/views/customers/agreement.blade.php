@php
    $agreementLabel = core()->getConfigData('general.gdpr.agreement.agreement_label');

    if (! $agreementLabel || trim($agreementLabel) === 'I agree with the terms and conditions.') {
        $agreementLabel = trans('shop::auth.agreement');
    }
@endphp

<label class="louzir-checkbox-line louzir-agreement">
    <v-field
        name="agreement"
        type="checkbox"
        :value="1"
        rules="required"
        label="@lang('shop::app.customers.signup-form.terms-conditions')"
        v-slot="{ field }"
    >
        <input type="checkbox" name="agreement" v-bind="field" aria-required="true">
    </v-field>

    <span v-pre>{{ $agreementLabel }}</span>
</label>
