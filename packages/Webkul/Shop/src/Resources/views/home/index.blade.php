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
    @if(! empty($categories))
        <script>
            localStorage.setItem('categories', JSON.stringify(@json($categories)));
        </script>
    @endif
@endpush

@push ('styles')
    <style>
        .louzir-home {
            --louzir-ink: #1a1c1b;
            --louzir-muted: #5f6166;
            --louzir-soft: #f4f3f1;
            --louzir-card: #ffffff;
            --louzir-line: #e1ded8;
            --louzir-gold: #79573d;
            --louzir-gold-soft: #fdcead;
            background: #faf9f7;
            color: var(--louzir-ink);
            overflow: hidden;
        }

        .louzir-shell {
            width: min(100%, 1440px);
            margin: 0 auto;
            padding-inline: clamp(16px, 4vw, 64px);
        }

        .louzir-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            border-radius: 4px;
            background: var(--louzir-gold-soft);
            color: #2d1603;
            padding: 7px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .louzir-title {
            font-family: "DM Serif Display", Georgia, serif;
            color: var(--louzir-ink);
            letter-spacing: 0;
        }

        .louzir-hero {
            background: linear-gradient(135deg, #f4f3f1 0%, #ffffff 52%, #f0e5dc 100%);
            padding-block: clamp(42px, 7vw, 96px);
        }

        .louzir-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 560px);
            gap: clamp(28px, 5vw, 72px);
            align-items: center;
        }

        .louzir-hero h1 {
            max-width: 720px;
            margin-top: 18px;
            font-size: clamp(38px, 5vw, 68px);
            line-height: 1.05;
        }

        .louzir-hero h1 em {
            color: var(--louzir-gold);
            font-style: italic;
            font-weight: 400;
        }

        .louzir-hero p {
            max-width: 620px;
            margin-top: 18px;
            color: var(--louzir-muted);
            font-size: clamp(15px, 1.6vw, 18px);
            line-height: 1.75;
        }

        .louzir-actions,
        .louzir-socials {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .louzir-actions {
            margin-top: 28px;
        }

        .louzir-socials {
            margin-top: 24px;
            align-items: center;
        }

        .louzir-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            border-radius: 4px;
            padding: 0 22px;
            font-size: 14px;
            font-weight: 700;
            transition: .2s ease;
        }

        .louzir-button.primary {
            background: #000;
            color: #fff;
        }

        .louzir-button.secondary,
        .louzir-chip {
            background: #fff;
            color: var(--louzir-ink);
            border: 1px solid var(--louzir-line);
        }

        .louzir-button:hover,
        .louzir-chip:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .08);
        }

        .louzir-chip {
            border-radius: 4px;
            padding: 6px 11px;
            font-size: 12px;
            font-weight: 600;
        }

        .louzir-visual {
            position: relative;
            min-height: 540px;
            border-radius: 8px;
            overflow: hidden;
            background: #ddd;
            box-shadow: 0 28px 70px rgba(20, 27, 43, .16);
        }

        .louzir-visual img {
            width: 100%;
            height: 100%;
            min-height: 540px;
            object-fit: cover;
            display: block;
        }

        .louzir-visual-card {
            position: absolute;
            left: 24px;
            right: 24px;
            bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            border-radius: 6px;
            background: rgba(255, 255, 255, .94);
            backdrop-filter: blur(14px);
            padding: 18px;
        }

        .louzir-strip,
        .louzir-products,
        .louzir-community {
            padding-block: clamp(38px, 6vw, 72px);
        }

        .louzir-strip {
            background: #fff;
            border-block: 1px solid var(--louzir-line);
        }

        .louzir-strip-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .louzir-feature {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .louzir-feature-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            border-radius: 999px;
            background: var(--louzir-gold-soft);
            color: #2d1603;
            font-size: 22px;
        }

        .louzir-section-head {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 24px;
            margin-bottom: 28px;
        }

        .louzir-section-head h2 {
            font-size: clamp(30px, 3.4vw, 44px);
            line-height: 1.1;
        }

        .louzir-section-head p {
            max-width: 460px;
            color: var(--louzir-muted);
            line-height: 1.7;
        }

        .louzir-bento {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 22px;
        }

        .louzir-card {
            position: relative;
            min-height: 360px;
            border-radius: 8px;
            overflow: hidden;
            background: #ddd;
        }

        .louzir-card.wide {
            grid-column: span 7;
        }

        .louzir-card.narrow {
            grid-column: span 5;
        }

        .louzir-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            inset: 0;
            transition: transform .6s ease;
        }

        .louzir-card:hover img {
            transform: scale(1.05);
        }

        .louzir-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, .74), rgba(0, 0, 0, .12), transparent);
        }

        .louzir-card-content {
            position: absolute;
            z-index: 1;
            left: 24px;
            right: 24px;
            bottom: 24px;
            color: #fff;
        }

        .louzir-card-content h3 {
            margin-top: 6px;
            font-family: "DM Serif Display", Georgia, serif;
            font-size: clamp(24px, 2.4vw, 34px);
        }

        .louzir-card-content p {
            max-width: 520px;
            margin-top: 8px;
            color: rgba(255, 255, 255, .82);
            line-height: 1.6;
        }

        .louzir-boutiques {
            background: #fff;
            padding-block: clamp(38px, 6vw, 72px);
        }

        .louzir-boutique-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
            align-items: stretch;
        }

        .louzir-boutique-card,
        .louzir-community-card {
            border: 1px solid var(--louzir-line);
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 16px 42px rgba(0, 0, 0, .06);
        }

        .louzir-boutique-card iframe {
            width: 100%;
            height: 224px;
            display: block;
            border: 0;
        }

        .louzir-map-preview {
            position: relative;
        }

        .louzir-map-preview iframe {
            pointer-events: none;
        }

        .louzir-map-preview a {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .louzir-boutique-info {
            padding: 26px;
        }

        .louzir-boutique-info h3 {
            font-family: "DM Serif Display", Georgia, serif;
            font-size: 24px;
            line-height: 1.3;
        }

        .louzir-boutique-info p {
            margin-top: 10px;
            color: var(--louzir-muted);
            line-height: 1.7;
        }

        .louzir-boutique-actions { display: flex; flex-wrap: wrap; gap: 10px; padding: 20px 26px; }
        .louzir-boutique-card { min-width: 0; }
        .louzir-boutique-info { min-height: 126px; }
        .louzir-boutique-actions .primary { flex: 1; }
        .louzir-home img { image-rendering: auto; }
        .louzir-community-card {
            padding: clamp(24px, 4vw, 42px);
            background: linear-gradient(135deg, #141b2b, #2f2524);
            color: #fff;
        }

        .louzir-community-card p {
            max-width: 660px;
            margin-top: 12px;
            color: rgba(255, 255, 255, .76);
            line-height: 1.7;
        }

        .louzir-community-card .louzir-title { color: #fff; }

        @media (max-width: 1180px) {
            .louzir-hero-grid {
                grid-template-columns: 1fr;
            }

            .louzir-visual,
            .louzir-visual img {
                min-height: 440px;
            }
        }

        @media (max-width: 900px) {
            .louzir-strip-grid,
            .louzir-section-head {
                grid-template-columns: 1fr;
                display: grid;
            }

            .louzir-bento {
                grid-template-columns: 1fr;
            }

            .louzir-card.wide,
            .louzir-card.narrow {
                grid-column: auto;
            }

            .louzir-card {
                min-height: 320px;
            }
        }

        @media (max-width: 640px) {
            .louzir-boutique-grid { grid-template-columns: 1fr; }
            .louzir-boutique-info { min-height: auto; padding: 20px; }
            .louzir-boutique-actions { padding: 20px; }
            .louzir-visual,
            .louzir-visual img {
                min-height: 360px;
            }

            .louzir-visual-card {
                left: 14px;
                right: 14px;
                bottom: 14px;
                align-items: flex-start;
                flex-direction: column;
            }

            .louzir-actions .louzir-button {
                width: 100%;
            }
        }
    </style>
@endpush

<x-shop::layouts :has-feature="false">
    <x-slot:title>
        {{  $channel->home_seo['meta_title'] ?? 'Louzir Mode Intime' }}
    </x-slot>

    <div class="louzir-home" v-pre>
        <section class="louzir-hero">
            <div class="louzir-shell louzir-hero-grid">
                <div>
                    <span class="louzir-kicker">{{ $homeContent->value('hero-kicker') }}</span>

                    <h1 class="louzir-title">
                        {{ $homeContent->value('hero-title-start') }}<br>
                        <em>{{ $homeContent->value('hero-title-emphasis') }}</em>
                    </h1>

                    <p>
                        {{ $homeContent->value('hero-description') }}
                    </p>

                    <div class="louzir-actions">
                        <a href="{{ $homeContent->value('hero-discover-url') }}" class="louzir-button primary">{{ $homeContent->value('hero-discover') }}</a>
                        <a href="{{ $homeContent->value('hero-stores-url') }}" class="louzir-button secondary">{{ $homeContent->value('hero-stores') }}</a>
                    </div>

                    <div class="louzir-socials">
                        <span class="text-xs font-semibold uppercase tracking-widest text-gray-500">{{ $homeContent->value('official-socials') }}</span>
                        <a class="louzir-chip" href="{{ $homeContent->value('facebook-url') }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                        <a class="louzir-chip" href="{{ $homeContent->value('linktree-url') }}" target="_blank" rel="noopener noreferrer">Linktree</a>
                    </div>
                </div>

                <div class="louzir-visual">
                    <img
                        src="{{ $homeContent->value('image-1') }}"
                            alt="{{ $homeContent->value('hero-image-alt') }}"
                    >

                    <div class="louzir-visual-card">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-widest text-[#79573d]">{{ $homeContent->value('arrival') }}</span>
                            <p class="mt-1 font-semibold">{{ $homeContent->value('soft-fabrics') }}</p>
                        </div>

                        <strong>{{ $homeContent->value('edition') }}</strong>
                    </div>
                </div>
            </div>
        </section>

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
