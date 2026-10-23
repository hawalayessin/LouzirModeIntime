{!! view_render_event('bagisto.shop.layout.footer.before') !!}
@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')

@php
    $channel = core()->getCurrentChannel();
    $customization = $themeCustomizationRepository->findOneWhere([
        'type' => 'footer_links',
        'status' => 1,
        'theme_code' => $channel->theme,
        'channel_id' => $channel->id,
    ]);

    $hiddenPolicyPaths = [
        '/page/privacy-policy',
        '/page/payment-policy',
        '/page/shipping-policy',
        '/page/refund-policy',
        '/page/return-policy',
    ];

    $footerLinkSections = collect($customization?->options ?? [])
        ->map(fn ($section) => collect($section)
            ->reject(fn ($link) => in_array(rtrim(parse_url($link['url'], PHP_URL_PATH) ?? '', '/'), $hiddenPolicyPaths, true))
            ->sortBy('sort_order'))
        ->filter(fn ($section) => $section->isNotEmpty());
@endphp

<footer class="louzir-footer">
    <div class="louzir-footer-inner">
        <div>
            <h2>LOUZIR</h2>
            <p>Mode Intime</p>
            <p><em>@lang('shop::app.home.storefront.footer-slogan')</em></p>
            <div class="louzir-footer-contact">
                <a href="mailto:louzirmodeintime@gmail.com">louzirmodeintime@gmail.com</a>
                <a href="tel:+21625293194">+216 25 293 194</a>
            </div>
            <div class="louzir-footer-social">
                <a href="https://www.instagram.com/louzir.modeintime" target="_blank" rel="noopener noreferrer">Instagram</a>
                <a href="https://linktr.ee/louzirmodeintime" target="_blank" rel="noopener noreferrer">@lang('shop::app.home.storefront.footer-socials')</a>
            </div>
        </div>
        <div>
            <h3>@lang('shop::app.home.storefront.footer-stores')</h3>
            <div class="louzir-footer-store">
                <strong>@lang('shop::app.home.storefront.footer-sahloul-store')</strong>
                <p>@lang('shop::app.home.storefront.footer-sahloul-address')</p>
                <a href="https://www.google.com/maps?q=35.8382447,10.5943201" target="_blank" rel="noopener noreferrer">@lang('shop::app.home.storefront.footer-sahloul-directions')</a>
            </div>
            <div class="louzir-footer-store">
                <strong>@lang('shop::app.home.storefront.footer-jemmel-store')</strong>
                <p>@lang('shop::app.home.storefront.footer-jemmel-address')</p>
                <a href="https://maps.app.goo.gl/id3Bdi6seqrr5TLX9" target="_blank" rel="noopener noreferrer">@lang('shop::app.home.storefront.footer-jemmel-directions')</a>
            </div>
        </div>
        @foreach ($footerLinkSections as $footerLinkSection)
            <nav v-pre>
                @foreach ($footerLinkSection as $link)
                    <a href="{{ $link['url'] }}">{{ $link['title'] }}</a>
                @endforeach
            </nav>
        @endforeach
        <section class="louzir-footer-newsletter" aria-labelledby="footer-newsletter-title">
            <h3 id="footer-newsletter-title">@lang('shop::app.components.layouts.footer.subscribe-newsletter')</h3>
            <p>@lang('shop::app.components.layouts.footer.subscribe-stay-touch')</p>

            <v-footer-newsletter v-slot="{ subscribe, isSubmitting, failed }">
            <x-shop::form :action="route('shop.subscription.store')" @submit="subscribe">
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label for="footer-newsletter-email">
                        @lang('shop::app.components.layouts.footer.email')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="email"
                        id="footer-newsletter-email"
                        name="email"
                        rules="required|email"
                        :value="old('email')"
                        :label="trans('shop::app.components.layouts.footer.email')"
                        :placeholder="trans('shop::app.components.layouts.footer.email')"
                        autocomplete="email"
                        aria-required="true"
                    />

                    <x-shop::form.control-group.error control-name="email" />
                </x-shop::form.control-group>

                <p v-if="failed" role="alert">@lang('shop::app.search.images.index.something-went-wrong')</p>

                <button type="submit" class="louzir-newsletter-submit" :disabled="isSubmitting" :aria-busy="isSubmitting">
                    @lang('shop::app.components.layouts.footer.subscribe')
                </button>
            </x-shop::form>
            </v-footer-newsletter>
        </section>
    </div>
    <div class="louzir-footer-bottom">
        {!! view_render_event('bagisto.shop.layout.footer.footer_text.before') !!}
        <p>
            @if (core()->getConfigData('general.content.footer.copyright_content'))
                {!! core()->getConfigData('general.content.footer.copyright_content') !!}
            @else
                @lang('shop::app.components.layouts.footer.footer-text', ['current_year' => date('Y')])
            @endif
        </p>
        {!! view_render_event('bagisto.shop.layout.footer.footer_text.after') !!}
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}

@pushOnce('scripts')
    <script type="module">
        app.component('v-footer-newsletter', {
            template: '<slot :subscribe="subscribe" :is-submitting="isSubmitting" :failed="failed"></slot>',

            data() {
                return { isSubmitting: false, failed: false };
            },

            methods: {
                async subscribe(values, { evt }) {
                    if (this.isSubmitting) return;

                    this.isSubmitting = true;
                    this.failed = false;

                    try {
                        const response = await fetch(@json(route('shop.subscription.csrf-token')), {
                            credentials: 'same-origin',
                            cache: 'no-store',
                            headers: { Accept: 'application/json' },
                        });

                        if (! response.ok) throw new Error('Session refresh failed');

                        const { token } = await response.json();
                        if (! token) throw new Error('Missing session token');

                        const form = evt.target;
                        form.elements.namedItem('_token').value = token;
                        HTMLFormElement.prototype.submit.call(form);
                    } catch {
                        this.failed = true;
                        this.isSubmitting = false;
                    }
                },
            },
        });
    </script>
@endPushOnce
