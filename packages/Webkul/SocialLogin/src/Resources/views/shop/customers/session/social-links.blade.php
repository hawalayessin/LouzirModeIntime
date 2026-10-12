<div class="louzir-social-login">
    @if (core()->getConfigData('customer.settings.social_login.enable_google'))
        @php
            $provider = 'google';
        @endphp

        <a
            href="{{ route('customer.social-login.index', $provider) }}"
            aria-label="Google"
        >
            <span>
                @include('social_login::icons.' . $provider)
            </span>

            <span>
                {{ app()->getLocale() === 'fr' ? 'Continuer avec Google' : 'Continue with Google' }}
            </span>
        </a>
    @endif
</div>
