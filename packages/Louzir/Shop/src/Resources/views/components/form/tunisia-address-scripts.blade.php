@include('shop::components.form.tunisia-location-select')

@pushOnce('scripts')
    <script type="text/x-template" id="v-tunisia-address-template">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
            <x-shop::form.control-group.control type="hidden" ::name="fieldName('country')" value="TN" rules="required" />

            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.16em] text-gray-500">
                        @lang('shop::tunisia.country')
                    </p>
                    <h3 class="mt-1 text-base font-semibold text-gray-900">@lang('shop::tunisia.country')</h3>
                </div>
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    @lang('shop::app.customers.account.addresses.create.country')
                </span>
            </div>

            <p v-if="!regions.length" role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                @lang('shop::tunisia.unavailable')
            </p>

            <div class="space-y-5">
                <x-shop::form.control-group class="mb-0">
                    <x-shop::form.control-group.label class="required" ::for="fieldId('state')">
                        @lang('shop::tunisia.governorate')
                    </x-shop::form.control-group.label>
                    <v-field :name="fieldName('state')" v-model="state" rules="required"
                        label="@lang('shop::tunisia.governorate')" v-slot="{ field, errors, handleBlur }">
                        <input type="hidden" v-bind="field">
                        <v-tunisia-location-select
                            :id="fieldId('state')" v-model="state" :options="regionOptions"
                            placeholder="@lang('shop::tunisia.select-governorate')"
                            :invalid="errors.length > 0" @blur="handleBlur"
                        ></v-tunisia-location-select>
                    </v-field>
                    <x-shop::form.control-group.error ::name="fieldName('state')" />
                </x-shop::form.control-group>

                <x-shop::form.control-group class="mb-0">
                    <x-shop::form.control-group.label class="required" ::for="fieldId('locality')">
                        @lang('shop::tunisia.locality')
                    </x-shop::form.control-group.label>
                    <v-field :name="fieldName('locality')" v-model="locality" rules="required"
                        label="@lang('shop::tunisia.locality')" v-slot="{ field, errors, handleBlur }">
                        <input type="hidden" v-bind="field">
                        <v-tunisia-location-select
                            :id="fieldId('locality')" v-model="locality" :options="localityOptions"
                            placeholder="@lang('shop::tunisia.select-locality')"
                            :disabled="!state || !localities.length"
                            :invalid="errors.length > 0" @blur="handleBlur"
                        ></v-tunisia-location-select>
                    </v-field>
                    <v-field type="hidden" :name="fieldName('city')" :model-value="selectedLocality?.name || ''" rules="required" label="@lang('shop::tunisia.locality')"></v-field>
                    <x-shop::form.control-group.error ::name="fieldName('locality')" />
                    <x-shop::form.control-group.error ::name="fieldName('city')" />
                </x-shop::form.control-group>
            </div>

            <div v-if="selectedLocality" class="mt-5 flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-800">
                <span class="font-medium">@lang('shop::app.checkout.onepage.address.postcode')</span>
                <span class="rounded-md bg-white px-2 py-1 font-semibold text-emerald-700">@{{ selectedLocality.postcode }}</span>
            </div>

            <v-field type="hidden" :name="fieldName('postcode')" :model-value="selectedLocality?.postcode || ''" rules="required|digits:4" label="@lang('shop::app.checkout.onepage.address.postcode')"></v-field>
            <x-shop::form.control-group.error ::name="fieldName('postcode')" />
        </div>
    </script>

    <script type="module">
        app.component('v-tunisia-address', {
            template: '#v-tunisia-address-template',
            props: {
                initialState: { type: String, default: '' },
                initialCity: { type: String, default: '' },
                initialPostcode: { type: String, default: '' },
                fieldPrefix: { type: String, default: '' },
            },
            data() {
                const regions = @json(app(\Webkul\Shop\Helpers\TunisiaMunicipalities::class)->all());
                const region = regions.find(region => region.value === this.initialState);
                const index = region?.localities.findIndex(place =>
                    place.name === this.initialCity && place.postcode === this.initialPostcode
                ) ?? -1;

                return {
                    regions,
                    state: region?.value || '',
                    locality: index < 0 ? '' : String(index),
                };
            },
            computed: {
                regionOptions() {
                    return this.regions.map(region => ({ value: region.value, label: this.label(region) }));
                },
                localityOptions() {
                    return this.localities.map((place, index) => ({ value: String(index), label: this.label(place) }));
                },
                localities() {
                    return this.regions.find(region => region.value === this.state)?.localities || [];
                },
                selectedLocality() {
                    return this.locality === '' ? null : this.localities[Number(this.locality)];
                },
            },
            watch: {
                state() {
                    this.locality = '';
                },
            },
            methods: {
                fieldName(name) {
                    return this.fieldPrefix ? this.fieldPrefix + '.' + name : name;
                },
                fieldId(name) {
                    return 'tn-' + (this.fieldPrefix || 'profile') + '-' + name;
                },
                label(item) {
                    return @json(app()->getLocale()) === 'ar' ? item.nameAr : item.name;
                },
            },
        });
    </script>
@endPushOnce
