@inject('homeContent', 'Webkul\Shop\Helpers\HomeContent')

@php
    $channel = core()->getCurrentChannel();
@endphp

<!-- SEO Meta Content -->
@push ('meta')
    <meta
        name="title"
        content="{{ $channel->home_seo['meta_title'] ?? '' }}"
    />

    <meta
        name="description"
        content="{{ $channel->home_seo['meta_description'] ?? '' }}"
    />

    <meta
        name="keywords"
        content="{{ $channel->home_seo['meta_keywords'] ?? '' }}"
    />
@endPush

@push('scripts')
    @include('shop::home.motion')

    @if(! empty($categories))
        <script>
            localStorage.setItem('categories', JSON.stringify(@json($categories)));
        </script>
    @endif
@endpush

@push('styles')
    @include('shop::home.styles')
@endpush

<x-shop::layouts :has-feature="false">
    <x-slot:title>
        {{  $channel->home_seo['meta_title'] ?? 'Louzir Mode Intime' }}
    </x-slot>

    <div class="louzir-home" v-pre>
        <section class="louzir-hero">
            <img
                class="louzir-hero-photo"
                src="{{ $homeContent->value('image-1') }}"
                alt="{{ $homeContent->value('hero-image-alt') }}"
                width="1408"
                height="768"
                fetchpriority="high"
            >
            <div class="louzir-shell louzir-hero-grid">
                <div class="louzir-hero-copy">
                    <span class="louzir-kicker">{{ $homeContent->value('hero-kicker') }}</span>

                    <h1 class="louzir-title" translate="no">{{ config('app.name') }}</h1>

                    <p class="louzir-hero-tagline">
                        {{ $homeContent->value('hero-title-start') }}
                        {{ $homeContent->value('hero-title-emphasis') }}
                    </p>

                    <p class="louzir-hero-description">
                        {{ $homeContent->value('hero-description') }}
                    </p>

                    <div class="louzir-actions">
                        <a href="{{ $homeContent->value('hero-discover-url') }}" class="louzir-button primary">{{ $homeContent->value('hero-discover') }}</a>
                        <a href="{{ $homeContent->value('hero-stores-url') }}" class="louzir-button secondary">{{ $homeContent->value('hero-stores') }}</a>
                    </div>

                    <div class="louzir-socials">
                        <span>{{ $homeContent->value('official-socials') }}</span>
                        <a class="louzir-chip" href="{{ $homeContent->value('facebook-url') }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                        <a class="louzir-chip" href="{{ $homeContent->value('linktree-url') }}" target="_blank" rel="noopener noreferrer">Linktree</a>
                    </div>
                </div>

            </div>
        </section>

        <div class="louzir-shell louzir-edition">
            <div>
                <span>{{ $homeContent->value('arrival') }}</span>
                <p>{{ $homeContent->value('soft-fabrics') }}</p>
            </div>
            <strong>{{ $homeContent->value('edition') }}</strong>
        </div>

        <section class="louzir-strip">
            <div class="louzir-shell louzir-strip-grid">
                <div class="louzir-feature">
                    <span class="louzir-feature-icon">✦</span>
                    <div>
                        <h3 class="font-semibold">{{ $homeContent->value('feature-fabrics-title') }}</h3>
                        <p class="mt-1 text-sm leading-6 text-gray-600">{{ $homeContent->value('feature-fabrics-description') }}</p>
                    </div>
                </div>

                <div class="louzir-feature">
                    <span class="louzir-feature-icon">♡</span>
                    <div>
                        <h3 class="font-semibold">{{ $homeContent->value('feature-family-title') }}</h3>
                        <p class="mt-1 text-sm leading-6 text-gray-600">{{ $homeContent->value('feature-family-description') }}</p>
                    </div>
                </div>

                <div class="louzir-feature">
                    <span class="louzir-feature-icon">⌂</span>
                    <div>
                        <h3 class="font-semibold">{{ $homeContent->value('feature-boutique-title') }}</h3>
                        <p class="mt-1 text-sm leading-6 text-gray-600">{{ $homeContent->value('feature-boutique-description') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="louzir-products" id="collections">
            <div class="louzir-shell">
                <div class="louzir-section-head">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-widest text-[#79573d]">{{ $homeContent->value('collections-kicker') }}</span>
                        <h2 class="louzir-title">{{ $homeContent->value('collections-title') }}</h2>
                    </div>

                    <p>{{ $homeContent->value('collections-description') }}</p>
                </div>

                <div class="louzir-bento">
                    <article class="louzir-card wide">
                        <img src="{{ $homeContent->value('image-2') }}" alt="{{ $homeContent->value('feminine-image-alt') }}">
                        <div class="louzir-card-content">
                            <span class="text-xs font-semibold uppercase tracking-widest text-[#fdcead]">{{ $homeContent->value('feminine-kicker') }}</span>
                            <h3>{{ $homeContent->value('feminine-title') }}</h3>
                            <p>{{ $homeContent->value('feminine-description') }}</p>
                        </div>
                        @if ($homeContent->value('feminine-url'))
                            <a href="{{ $homeContent->value('feminine-url') }}" aria-label="{{ $homeContent->value('feminine-title') }}" style="position: absolute; inset: 0; z-index: 2;"></a>
                        @endif
                    </article>

                    <article class="louzir-card narrow">
                        <img src="{{ $homeContent->value('image-3') }}" alt="{{ $homeContent->value('lounge-image-alt') }}">
                        <div class="louzir-card-content">
                            <span class="text-xs font-semibold uppercase tracking-widest text-[#fdcead]">{{ $homeContent->value('lounge-kicker') }}</span>
                            <h3>{{ $homeContent->value('lounge-title') }}</h3>
                            <p>{{ $homeContent->value('lounge-description') }}</p>
                        </div>
                        @if ($homeContent->value('lounge-url'))
                            <a href="{{ $homeContent->value('lounge-url') }}" aria-label="{{ $homeContent->value('lounge-title') }}" style="position: absolute; inset: 0; z-index: 2;"></a>
                        @endif
                    </article>

                    <article class="louzir-card narrow">
                        <img src="{{ $homeContent->value('image-4') }}" alt="{{ $homeContent->value('family-image-alt') }}">
                        <div class="louzir-card-content">
                            <span class="text-xs font-semibold uppercase tracking-widest text-[#fdcead]">{{ $homeContent->value('family-kicker') }}</span>
                            <h3>{{ $homeContent->value('family-title') }}</h3>
                            <p>{{ $homeContent->value('family-description') }}</p>
                        </div>
                        @if ($homeContent->value('family-url'))
                            <a href="{{ $homeContent->value('family-url') }}" aria-label="{{ $homeContent->value('family-title') }}" style="position: absolute; inset: 0; z-index: 2;"></a>
                        @endif
                    </article>

                    <article class="louzir-card wide">
                        <img src="{{ $homeContent->value('image-5') }}" alt="{{ $homeContent->value('new-arrivals-image-alt') }}">
                        <div class="louzir-card-content">
                            <span class="text-xs font-semibold uppercase tracking-widest text-[#fdcead]">{{ $homeContent->value('new-arrivals-kicker') }}</span>
                            <h3>{{ $homeContent->value('new-arrivals-title') }}</h3>
                            <p>{{ $homeContent->value('new-arrivals-description') }}</p>
                        </div>
                        @if ($homeContent->value('new-arrivals-url'))
                            <a href="{{ $homeContent->value('new-arrivals-url') }}" aria-label="{{ $homeContent->value('new-arrivals-title') }}" style="position: absolute; inset: 0; z-index: 2;"></a>
                        @endif
                    </article>
                </div>
            </div>
        </section>

        <section class="louzir-boutiques" id="boutiques">
            <div class="louzir-shell">
                <div class="louzir-section-head">
                    <div>
                        <span class="louzir-kicker">{{ $homeContent->value('addresses-kicker') }}</span>
                        <h2 class="louzir-title">{{ $homeContent->value('addresses-title') }}</h2>
                    </div>
                    <p>{{ $homeContent->value('addresses-description') }}</p>
                </div>
                <div class="louzir-boutique-grid">
                    <article class="louzir-boutique-card">
                        <div class="louzir-boutique-info">
                            <h3>{{ $homeContent->value('sahloul-name') }}</h3>
                            <p>{{ $homeContent->value('sahloul-address') }}</p>
                        </div>
                        <div class="louzir-map-preview">
                            <iframe src="{{ $homeContent->value('sahloul-map-url') }}"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ $homeContent->value('sahloul-map-title') }}"></iframe>
                            <a href="{{ $homeContent->value('sahloul-directions-url') }}"
                                target="_blank" rel="noopener noreferrer" aria-label="{{ $homeContent->value('sahloul-map-label') }}"></a>
                        </div>
                        <div class="louzir-boutique-actions">
                            <a class="louzir-button primary" href="{{ $homeContent->value('sahloul-directions-url') }}" target="_blank" rel="noopener noreferrer">{{ $homeContent->value('sahloul-directions') }}</a>
                            <a class="louzir-button secondary" href="{{ $homeContent->value('phone-url') }}">{{ $homeContent->value('call-us') }}</a>
                        </div>
                    </article>
                    <article class="louzir-boutique-card">
                        <div class="louzir-boutique-info">
                            <h3>{{ $homeContent->value('jemmel-name') }}</h3>
                            <p>{{ $homeContent->value('jemmel-address') }}</p>
                        </div>
                        <div class="louzir-map-preview">
                            <iframe src="{{ $homeContent->value('jemmel-map-url') }}"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ $homeContent->value('jemmel-map-title') }}"></iframe>
                            <a href="{{ $homeContent->value('jemmel-directions-url') }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $homeContent->value('jemmel-map-label') }}"></a>
                        </div>
                        <div class="louzir-boutique-actions">
                            <a class="louzir-button primary" href="{{ $homeContent->value('jemmel-directions-url') }}" target="_blank" rel="noopener noreferrer">{{ $homeContent->value('jemmel-directions') }}</a>
                            <a class="louzir-button secondary" href="{{ $homeContent->value('phone-url') }}">{{ $homeContent->value('call-us') }}</a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="louzir-community">
            <div class="louzir-shell">
                <div class="louzir-community-card">
                    <span class="text-xs font-semibold uppercase tracking-widest text-[#fdcead]">{{ $homeContent->value('community-kicker') }}</span>
                    <h2 class="louzir-title mt-3 text-3xl text-white md:text-4xl">{{ $homeContent->value('community-title') }}</h2>
                    <p>{{ $homeContent->value('community-description') }}</p>
                    <div class="louzir-actions">
                        <a class="louzir-button secondary" href="{{ $homeContent->value('facebook-url') }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                        <a class="louzir-button secondary" href="{{ $homeContent->value('linktree-url') }}" target="_blank" rel="noopener noreferrer">Linktree</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-shop::layouts>
