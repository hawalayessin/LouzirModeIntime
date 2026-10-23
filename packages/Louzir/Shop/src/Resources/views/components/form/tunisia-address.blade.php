@props(['address' => null])

<v-tunisia-address
    :initial-state='@json(old("state", $address?->state ?? ""))'
    :initial-city='@json(old("city", $address?->city ?? ""))'
    :initial-postcode='@json(old("postcode", $address?->postcode ?? ""))'
></v-tunisia-address>

@include('shop::components.form.tunisia-address-scripts')
