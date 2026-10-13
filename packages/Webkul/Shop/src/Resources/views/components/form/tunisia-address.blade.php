@props(['address' => null])

<v-tunisia-address
    :initial-state='@json(old("state", $address?->state ?? ""))'
    :initial-city='@json(old("city", $address?->city ?? ""))'
    :initial-postcode='@json(old("postcode", $address?->postcode ?? ""))'
></v-tunisia-address>

@pushOnce('scripts')
    <script type="text/x-template" id="v-tunisia-address-template">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
            <input type="hidden" name="country" value="TN">

            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.16em] text-gray-500">
                        @lang('shop::tunisia.country')
                    </p>
                    <h3 class="mt-1 text-base font-semibold text-gray-900">Tunisie</h3>
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
                    <x-shop::form.control-group.label class="required" for="tn-state">
                        @lang('shop::tunisia.governorate')
                    </x-shop::form.control-group.label>
                    <x-shop::form.control-group.control
                        type="select" id="tn-state" name="state" rules="required"
                        v-model="state" :label="trans('shop::tunisia.governorate')"
                        class="!rounded-xl !border-gray-300 !bg-white !px-4 !py-3.5 shadow-sm transition focus:!border-brandColor focus:!ring-2 focus:!ring-brandColor/20"
                    >
                        <option value="">@lang('shop::tunisia.select-governorate')</option>
                        <option v-for="region in regions" :key="region.value" :value="region.value">
                            @{{ label(region) }}
                        </option>
                    </x-shop::form.control-group.control>
                    <x-shop::form.control-group.error control-name="state" />
                </x-shop::form.control-group>

                <x-shop::form.control-group class="mb-0">
                    <x-shop::form.control-group.label class="required" for="tn-locality">
                        @lang('shop::tunisia.locality')
                    </x-shop::form.control-group.label>
                    <x-shop::form.control-group.control
                        type="select" id="tn-locality" name="locality" rules="required"
                        v-model="locality" ::disabled="!state || !localities.length"
                        :label="trans('shop::tunisia.locality')"
                        class="!rounded-xl !border-gray-300 !bg-white !px-4 !py-3.5 shadow-sm transition focus:!border-brandColor focus:!ring-2 focus:!ring-brandColor/20 disabled:!cursor-not-allowed disabled:!bg-gray-100"
                    >
                        <option value="">@lang('shop::tunisia.select-locality')</option>
                        <option v-for="(place, index) in localities" :key="index" :value="String(index)">
                            @{{ label(place) }}
                        </option>
                    </x-shop::form.control-group.control>
                    <input type="hidden" name="city" :value="selectedLocality?.name || ''">
                    <x-shop::form.control-group.error control-name="locality" />
                    <x-shop::form.control-group.error control-name="city" />
                </x-shop::form.control-group>
            </div>

            <div v-if="selectedLocality" class="mt-5 flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-800">
                <span class="font-medium">Code postal</span>
                <span class="rounded-md bg-white px-2 py-1 font-semibold text-emerald-700">@{{ selectedLocality.postcode }}</span>
            </div>

            <input type="hidden" name="postcode" :value="selectedLocality?.postcode || ''">
            <x-shop::form.control-group.error control-name="postcode" />
        </div>
    </script>

    <script type="module">
        app.component('v-tunisia-address', {
            template: '#v-tunisia-address-template',
            props: ['initialState', 'initialCity', 'initialPostcode'],
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
                label(item) {
                    return @json(app()->getLocale()) === 'ar' ? item.nameAr : item.name;
                },
            },
        });
    </script>
@endPushOnce
