@php
    $signatureQuery = [
        'limit' => $homeContent->value('signature-limit'),
        'sort' => $homeContent->value('signature-sort'),
    ];

    if ((int) $homeContent->value('signature-category') > 0) {
        $signatureQuery['category_id'] = $homeContent->value('signature-category');
    }
@endphp

<section class="louzir-signature" id="catalogue" aria-labelledby="signature-title">
    <div class="louzir-shell">
        <div class="louzir-signature-head" v-pre>
            <span class="louzir-kicker">{{ $homeContent->value('signature-kicker') }}</span>
            <h2 class="louzir-title" id="signature-title">{{ $homeContent->value('signature-title') }}</h2>
            <p>{{ $homeContent->value('signature-description') }}</p>
        </div>
        <v-louzir-signature src="{{ route('shop.api.products.index', $signatureQuery) }}">
            <x-shop::shimmer.products.carousel :navigation-link="false" />
        </v-louzir-signature>
    </div>
</section>

@pushOnce('scripts')
    <script type="text/x-template" id="v-louzir-signature-template">
        <div :aria-busy="isLoading">
            <div v-if="isLoading" class="louzir-signature-loading" role="status">
                <span v-for="index in 4" :key="index" class="shimmer"></span>
            </div>
            <div v-else-if="hasError" class="louzir-signature-empty" role="status">
                <p>@lang('shop::home.signature-error')</p>
                <button type="button" class="louzir-button secondary" @click="getProducts">@lang('shop::home.retry')</button>
            </div>
            <div v-else-if="products.length" class="louzir-signature-grid" :data-single="products.length === 1 ? true : null">
                <x-shop::products.card class="louzir-signature-product" v-for="product in products" ::key="product.id" />
            </div>
            <p v-else class="louzir-signature-empty" role="status">@lang('shop::home.signature-empty')</p>
        </div>
    </script>

    <script type="module">
        app.component('v-louzir-signature', {
            template: '#v-louzir-signature-template',
            props: ['src'],
            data() {
                return { products: [], isLoading: true, hasError: false };
            },
            mounted() {
                this.getProducts();
            },
            methods: {
                getProducts() {
                    this.isLoading = true;
                    this.hasError = false;
                    this.$axios.get(this.src)
                        .then(response => {
                            this.products = response.data.data;
                        })
                        .catch(() => { this.hasError = true; })
                        .finally(() => {
                            this.isLoading = false;
                            this.$nextTick(() => this.$el.dispatchEvent(new CustomEvent('louzir-products-ready', { bubbles: true })));
                        });
                },
            },
        });
    </script>
@endPushOnce
