@inject('homeContent', 'Webkul\Shop\Helpers\HomeContent')

@php
    $channel = core()->getCurrentChannel();
    $marqueeItems = collect(range(1, 4))->map(fn ($index) => $homeContent->value('marquee-item-'.$index))->filter();
@endphp

@push('meta')
    <meta name="title" content="{{ $channel->home_seo['meta_title'] ?? '' }}">
    <meta name="description" content="{{ $channel->home_seo['meta_description'] ?? '' }}">
    <meta name="keywords" content="{{ $channel->home_seo['meta_keywords'] ?? '' }}">
@endpush

@push('styles')
    @include('shop::home.styles')
@endpush

@push('scripts')
    @include('shop::home.motion')
    @include('shop::home.hero-carousel-script')

    @if (! empty($categories))
        <script>
            localStorage.setItem('categories', JSON.stringify(@json($categories)));
        </script>
    @endif
@endpush

<x-shop::layouts :has-feature="false">
    <x-slot:title>{{ $channel->home_seo['meta_title'] ?? config('app.name') }}</x-slot>

    <div class="louzir-home"
        data-motion-enabled="{{ $homeContent->value('motion-enabled') }}"
        data-motion-duration="{{ $homeContent->value('motion-duration') }}"
        data-motion-stagger="{{ $homeContent->value('motion-stagger') }}"
        style="--louzir-motion-duration: {{ $homeContent->value('motion-duration') }}ms">
        @if ($homeContent->value('marquee-enabled') === '1' && $marqueeItems->isNotEmpty())
            <section
                class="louzir-marquee"
                style="--marquee-duration: {{ $homeContent->value('marquee-speed') }}s; --marquee-background: {{ $homeContent->value('marquee-background') }}; --marquee-color: {{ $homeContent->value('marquee-color') }}; --marquee-accent: {{ $homeContent->value('marquee-accent') }}"
                aria-label="@lang('shop::home.marquee-label')"
                v-pre
            >
                <div class="louzir-marquee-window">
                    <div class="louzir-marquee-track">
                        @for ($copy = 0; $copy < 2; $copy++)
                            <div class="louzir-marquee-group" @if ($copy) aria-hidden="true" @endif>
                                @foreach ($marqueeItems as $message)
                                    <span>{{ $message }} <span class="icon-star" aria-hidden="true"></span></span>
                                @endforeach
                            </div>
                        @endfor
                    </div>
                </div>
                <button
                    type="button"
                    class="louzir-marquee-toggle"
                    aria-label="@lang('shop::home.pause')"
                    title="@lang('shop::home.pause')"
                    data-pause-label="@lang('shop::home.pause')"
                    data-resume-label="@lang('shop::home.resume')"
                    aria-pressed="false"
                ><span class="louzir-pause-symbol" aria-hidden="true"></span></button>
            </section>
        @endif

        @include('shop::home.hero-carousel')

        @if ($homeContent->value('services-enabled') === '1')
            <section class="louzir-strip" v-pre>
                <div class="louzir-shell louzir-strip-grid">
                    @foreach (['fabrics' => 'icon-star', 'family' => 'icon-heart', 'boutique' => 'icon-location'] as $service => $icon)
                        <div class="louzir-feature">
                            <span class="louzir-feature-icon {{ $icon }}" aria-hidden="true"></span>
                            <div>
                                <h3>{{ $homeContent->value('feature-'.$service.'-title') }}</h3>
                                <p>{{ $homeContent->value('feature-'.$service.'-description') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($homeContent->value('collections-enabled') === '1')
            <section class="louzir-products" id="collections" v-pre>
                <div class="louzir-shell">
                    <div class="louzir-section-head">
                        <div>
                            <span class="louzir-kicker">{{ $homeContent->value('collections-kicker') }}</span>
                            <h2 class="louzir-title">{{ $homeContent->value('collections-title') }}</h2>
                        </div>
                        <p>{{ $homeContent->value('collections-description') }}</p>
                    </div>
                    <div class="louzir-bento">
                        @foreach (['feminine' => 'image-2', 'lounge' => 'image-3', 'family' => 'image-4', 'new-arrivals' => 'image-5'] as $collection => $image)
                            <article class="louzir-card">
                                <div class="louzir-card-media">
                                    <img src="{{ $homeContent->value($image) }}" alt="{{ $homeContent->value($collection.'-image-alt') }}"
                                        width="1408" height="768" loading="lazy" decoding="async">
                                    <span class="louzir-media-badge">{{ $homeContent->value($collection.'-badge') }}</span>
                                </div>
                                <div class="louzir-card-content">
                                    <span class="louzir-kicker">{{ $homeContent->value($collection.'-kicker') }}</span>
                                    <h3>{{ $homeContent->value($collection.'-title') }}</h3>
                                    <p>{{ $homeContent->value($collection.'-description') }}</p>
                                </div>
                                @if ($homeContent->value($collection.'-url'))
                                    <a class="louzir-card-link" href="{{ $homeContent->value($collection.'-url') }}"
                                        aria-label="{{ $homeContent->value($collection.'-title') }}"></a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if ($homeContent->value('signature-enabled') === '1')
            @include('shop::home.signature')
        @endif

        @if ($homeContent->value('boutiques-enabled') === '1')
            <section class="louzir-boutiques" id="boutiques" v-pre>
                <div class="louzir-shell">
                    <div class="louzir-section-head">
                        <div>
                            <span class="louzir-kicker">{{ $homeContent->value('addresses-kicker') }}</span>
                            <h2 class="louzir-title">{{ $homeContent->value('addresses-title') }}</h2>
                        </div>
                        <p>{{ $homeContent->value('addresses-description') }}</p>
                    </div>
                    <div class="louzir-boutique-grid">
                        @foreach (['sahloul', 'jemmel'] as $store)
                            <article class="louzir-boutique-card">
                                <div class="louzir-boutique-info">
                                    <h3>{{ $homeContent->value($store.'-name') }}</h3>
                                    <p>{{ $homeContent->value($store.'-address') }}</p>
                                </div>
                                <a class="louzir-map-preview" href="{{ $homeContent->value($store.'-directions-url') }}"
                                    target="_blank" rel="noopener noreferrer">
                                    <img src="{{ $homeContent->value($store.'-image') }}" alt="{{ $homeContent->value($store.'-name') }}"
                                        loading="lazy" decoding="async">
                                    <span>@lang('shop::home.store-map') <span class="icon-arrow-right" aria-hidden="true"></span></span>
                                </a>
                                <div class="louzir-boutique-actions">
                                    <a class="louzir-button primary" href="{{ $homeContent->value($store.'-directions-url') }}" target="_blank" rel="noopener noreferrer">{{ $homeContent->value($store.'-directions') }}</a>
                                    <a class="louzir-button secondary" href="{{ $homeContent->value('phone-url') }}"><span class="icon-support" aria-hidden="true"></span>{{ $homeContent->value('call-us') }}</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if ($homeContent->value('community-enabled') === '1')
            <section class="louzir-community" v-pre>
                <div class="louzir-shell louzir-community-card">
                    <div>
                        <span class="louzir-kicker">{{ $homeContent->value('community-kicker') }}</span>
                        <h2 class="louzir-title">{{ $homeContent->value('community-title') }}</h2>
                        <p>{{ $homeContent->value('community-description') }}</p>
                    </div>
                    <div class="louzir-actions">
                        <a class="louzir-button secondary" href="{{ $homeContent->value('facebook-url') }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                        <a class="louzir-button secondary" href="{{ $homeContent->value('linktree-url') }}" target="_blank" rel="noopener noreferrer">Linktree</a>
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-shop::layouts>
