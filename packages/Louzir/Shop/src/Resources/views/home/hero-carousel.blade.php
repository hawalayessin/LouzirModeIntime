@php
    $heroSlides = $homeContent->heroSlides();
@endphp

@if ($homeContent->value('hero-enabled') === '1' && count($heroSlides))
    <section class="louzir-hero louzir-carousel" role="region" aria-roledescription="carousel"
        aria-label="@lang('shop::home.carousel-label')" tabindex="0"
        data-autoplay="{{ $homeContent->value('hero-autoplay') }}"
        data-interval="{{ $homeContent->value('hero-interval') }}" v-pre>
        <div class="louzir-slides">
            @foreach ($heroSlides as $slide)
                <div class="louzir-slide {{ $loop->first ? 'is-active' : '' }}" role="group"
                    aria-roledescription="slide" aria-label="{{ $loop->iteration }} / {{ count($heroSlides) }}"
                    aria-hidden="{{ $loop->first ? 'false' : 'true' }}" @if (! $loop->first) inert @endif
                    style="--hero-x: {{ $slide['x'] }}%; --hero-y: {{ $slide['y'] }}%; --hero-mobile-x: {{ $slide['mobile_x'] }}%; --hero-overlay: {{ $slide['overlay'] / 100 }}">
                    <img class="louzir-hero-photo" src="{{ $slide['image'] }}" alt="{{ $slide['alt'] }}"
                        width="1672" height="941" @if ($loop->first) fetchpriority="high" @else loading="eager" fetchpriority="low" @endif decoding="async">
                    <div class="louzir-shell louzir-hero-grid">
                        <div class="louzir-hero-copy">
                            <span class="louzir-kicker louzir-hero-kicker">{{ $slide['kicker'] }}</span>
                            @if ($loop->first)
                                <h1 class="louzir-title">{{ $slide['title'] }}</h1>
                            @else
                                <h2 class="louzir-title">{{ $slide['title'] }}</h2>
                            @endif
                            <p class="louzir-hero-tagline">{{ $slide['tagline'] }}</p>
                            <p class="louzir-hero-description">{{ $slide['description'] }}</p>
                            <div class="louzir-actions">
                                <a href="{{ $slide['url'] }}" class="louzir-button primary">{{ $slide['button'] }}</a>
                                <a href="{{ $homeContent->value('hero-stores-url') }}" class="louzir-button secondary">{{ $homeContent->value('hero-stores') }}</a>
                            </div>
                            <div class="louzir-socials">
                                <span>{{ $homeContent->value('official-socials') }}</span>
                                <a class="louzir-chip" href="{{ $homeContent->value('facebook-url') }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                                <a class="louzir-chip" href="{{ $homeContent->value('linktree-url') }}" target="_blank" rel="noopener noreferrer">Linktree</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if (count($heroSlides) > 1)
            <div class="louzir-carousel-controls" hidden>
                <div class="louzir-carousel-pagination">
                    <span class="louzir-carousel-counter" aria-live="off">01 / {{ str_pad(count($heroSlides), 2, '0', STR_PAD_LEFT) }}</span>
                    @foreach ($heroSlides as $slide)
                        <button type="button" class="louzir-carousel-dot" data-index="{{ $loop->index }}"
                            aria-label="@lang('shop::home.go-to-slide', ['number' => $loop->iteration])"
                            aria-current="{{ $loop->first ? 'true' : 'false' }}"><span></span></button>
                    @endforeach
                </div>
                <div class="louzir-carousel-navigation">
                    <button type="button" data-carousel-prev aria-label="@lang('shop::home.previous-slide')"><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6" /></svg></button>
                    <button type="button" data-carousel-toggle data-pause-label="@lang('shop::home.pause')"
                        data-resume-label="@lang('shop::home.resume')" aria-label="@lang('shop::home.pause')" aria-pressed="false"><span aria-hidden="true">Ⅱ</span></button>
                    <button type="button" data-carousel-next aria-label="@lang('shop::home.next-slide')"><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6" /></svg></button>
                </div>
            </div>
        @endif
    </section>
    <div class="louzir-shell louzir-edition" v-pre>
        <div><span>{{ $homeContent->value('arrival') }}</span><p>{{ $homeContent->value('soft-fabrics') }}</p></div>
        <strong>{{ $homeContent->value('edition') }}</strong>
    </div>
@endif
