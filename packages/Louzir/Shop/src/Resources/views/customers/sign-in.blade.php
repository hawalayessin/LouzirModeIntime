<!-- SEO Meta Content -->
@push('meta')
    <meta name="description" content="@lang('shop::app.customers.login-form.page-title')"/>

    <meta name="keywords" content="@lang('shop::app.customers.login-form.page-title')"/>
@endPush

@push('styles')
    @include('shop::customers.auth-styles')
@endpush

<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="false"
>
    <!-- Page Title -->
    <x-slot:title>
        @lang('shop::app.customers.login-form.page-title')
    </x-slot>

    @php
        $hasSocialLogin = false;

        $hasSocialLogin = (bool) core()->getConfigData('customer.settings.social_login.enable_google');
    @endphp

    <div class="louzir-auth-page">
        <header class="louzir-brand-header">
            <a
                href="{{ route('shop.home.index') }}"
                class="louzir-brand"
                aria-label="@lang('shop::app.customers.login-form.bagisto')"
            >
                <img
                    src="{{ core()->getCurrentChannel()->logo_url }}"
                    alt="{{ config('app.name') }}"
                    class="louzir-brand-logo"
                    width="190"
                    height="58"
                >
            </a>

            <span class="louzir-header-caption">L'elegance, tout simplement.</span>
        </header>

        <main class="louzir-auth-layout">
            <aside
                class="louzir-editorial-panel"
                aria-label="Collection LouzirModeIntime"
            >
                <img
                    src="{{ asset('assets/login-editorial.jpg') }}"
                    alt="Collection LouzirModeIntime"
                    width="1024"
                    height="1536"
                >

                <div class="louzir-editorial-label">
                    <span class="louzir-editorial-line"></span>
                    L'INTIME, AVEC ELEGANCE
                </div>

                <div class="louzir-editorial-copy">
                    <span class="louzir-editorial-eyebrow">UN INSTANT POUR VOUS</span>

                    <h2>
                        La douceur<br>
                        vous va si bien.
                    </h2>

                    <p>
                        Des essentiels delicats.<br>
                        Une elegance qui vous ressemble.
                    </p>
                </div>

                <div class="louzir-editorial-bottom">
                    <span>*</span>
                    <span>LOUZIR MODE INTIME</span>
                </div>
            </aside>

            <section
                class="louzir-form-panel"
                aria-labelledby="login-title"
            >
                <div class="louzir-form-content">
                    <p class="louzir-form-eyebrow">@lang('shop::app.customers.login-form.bagisto')</p>

                    <h1
                        id="login-title"
                        class="louzir-form-title"
                    >
                        @lang('shop::app.customers.login-form.page-title')
                    </h1>

                    <p class="louzir-form-intro">
                        @lang('shop::app.customers.login-form.form-login-text')
                    </p>

                    @if ($hasSocialLogin)
                        {!! view_render_event('bagisto.shop.customers.login_form_controls.after') !!}

                        <div class="louzir-form-divider">
                            <span></span>
                            {{ app()->getLocale() === 'fr' ? 'ou avec votre e-mail' : 'or with your email' }}
                            <span></span>
                        </div>
                    @endif

                    {!! view_render_event('bagisto.shop.customers.login.before') !!}

                    <div class="louzir-auth-form">
                        <x-shop::form :action="route('shop.customer.session.create')">
                            {!! view_render_event('bagisto.shop.customers.login_form_controls.before') !!}

                            @foreach (['error', 'warning', 'info'] as $messageType)
                                @if (session()->has($messageType))
                                    <div class="louzir-auth-alert" role="alert">
                                        {{ session($messageType) }}
                                    </div>
                                @endif
                            @endforeach

                            <!-- Email -->
                            <x-shop::form.control-group>
                                <x-shop::form.control-group.label class="required" for="email">
                                    @lang('shop::app.customers.login-form.email')
                                </x-shop::form.control-group.label>

                                <x-shop::form.control-group.control
                                    type="email"
                                    class="px-4 py-3"
                                    id="email"
                                    name="email"
                                    autocomplete="email"
                                    rules="required|email"
                                    :value="old('email')"
                                    :label="trans('shop::app.customers.login-form.email')"
                                    placeholder="vous@exemple.com"
                                    :aria-label="trans('shop::app.customers.login-form.email')"
                                    aria-required="true"
                                />

                                <x-shop::form.control-group.error control-name="email" />
                            </x-shop::form.control-group>

                            <!-- Password -->
                            <x-shop::form.control-group>
                                <x-shop::form.control-group.label class="required" for="password">
                                    @lang('shop::app.customers.login-form.password')
                                </x-shop::form.control-group.label>

                                <x-shop::form.control-group.control
                                    type="password"
                                    class="px-4 py-3"
                                    id="password"
                                    name="password"
                                    autocomplete="current-password"
                                    rules="required|min:6"
                                    value=""
                                    :label="trans('shop::app.customers.login-form.password')"
                                    :placeholder="trans('shop::app.customers.login-form.password')"
                                    :aria-label="trans('shop::app.customers.login-form.password')"
                                    aria-required="true"
                                />

                                <x-shop::form.control-group.error control-name="password" />
                            </x-shop::form.control-group>

                            <div class="louzir-form-options">
                                <label class="louzir-checkbox-line">
                                    <input
                                        type="checkbox"
                                        id="show-password"
                                        onchange="switchVisibility()"
                                    >

                                    @lang('shop::app.customers.login-form.show-password')
                                </label>

                                <a
                                    href="{{ route('shop.customers.forgot_password.create') }}"
                                    class="louzir-forgot-link"
                                >
                                    @lang('shop::app.customers.login-form.forgot-pass')
                                </a>
                            </div>

                            @if(
                                core()->getConfigData('general.gdpr.settings.enabled')
                                && core()->getConfigData('general.gdpr.agreement.enabled')
                            )
                                @include('shop::customers.agreement')

                                <x-shop::form.control-group.error control-name="agreement" />
                            @endif

                            <!-- Captcha -->
                            @if (core()->getConfigData('customer.captcha.credentials.status'))
                                <x-shop::form.control-group class="mt-5">
                                    {!! \Webkul\Customer\Facades\Captcha::render() !!}

                                    <x-shop::form.control-group.error control-name="recaptcha_token" />
                                </x-shop::form.control-group>
                            @endif

                            <!-- Submit Button -->
                            <button
                                class="louzir-login-button"
                                type="submit"
                            >
                                @lang('shop::app.customers.login-form.button-title')
                            </button>
                        </x-shop::form>
                    </div>

                    {!! view_render_event('bagisto.shop.customers.login.after') !!}

                    <div class="louzir-security-note">
                        <span>*</span>
                        <span>Votre espace, en toute confidentialite.</span>
                    </div>
                </div>

                <div class="louzir-form-bottom">
                    <span>Une touche de douceur, chaque jour.</span>
                </div>

                <p class="louzir-auth-switch">
                    @lang('shop::app.customers.login-form.new-customer')

                    <a
                        class="louzir-auth-link"
                        href="{{ route('shop.customers.register.index') }}"
                    >
                        @lang('shop::app.customers.login-form.create-your-account')
                    </a>
                </p>
            </section>
        </main>

        <footer class="louzir-auth-footer">
            <span>@lang('shop::app.customers.login-form.footer', ['current_year'=> date('Y') ])</span>

            <span>
                <span>*</span>
                Espace personnel
            </span>
        </footer>
    </div>

    @push('scripts')
        {!! \Webkul\Customer\Facades\Captcha::renderJS() !!}

        <script>
            function switchVisibility() {
                let passwordField = document.getElementById("password");

                passwordField.type = passwordField.type === "password"
                    ? "text"
                    : "password";
            }
        </script>
    @endpush
</x-shop::layouts>
