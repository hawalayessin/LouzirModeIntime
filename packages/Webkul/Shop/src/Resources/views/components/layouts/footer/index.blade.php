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
        @foreach ($customization?->options ?? [] as $footerLinkSection)
            <nav v-pre>
                @foreach (collect($footerLinkSection)->sortBy('sort_order') as $link)
                    <a href="{{ $link['url'] }}">{{ $link['title'] }}</a>
                @endforeach
            </nav>
        @endforeach
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
