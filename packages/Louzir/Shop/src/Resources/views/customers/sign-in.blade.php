<!-- SEO Meta Content -->
@push('meta')
    <meta name="description" content="@lang('shop::app.customers.login-form.page-title')"/>

    <meta name="keywords" content="@lang('shop::app.customers.login-form.page-title')"/>
@endPush

@push('styles')
    <style>
        html {
            overflow-y: scroll;
            scrollbar-gutter: stable;
        }

        .louzir-auth-page {
            --auth-bg: #faf9f7;
            --auth-card: #ffffff;
            --auth-ink: #1a1c1b;
            --auth-muted: #5f6166;
            --auth-soft: #f4f3f1;
            --auth-border: #e1ded8;
            --auth-accent: #79573d;
            --auth-shadow: 0 12px 48px rgba(121, 87, 61, .08);
            min-height: 100svh;
            padding: 0 32px;
            background: var(--auth-bg);
            color: var(--auth-ink);
            font-family: "Poppins", "Segoe UI", sans-serif;
            letter-spacing: 0;
        }

        .louzir-brand-header {
            display: grid;
            grid-template-columns: 1fr;
            align-items: center;
            justify-items: center;
            max-width: 1280px;
            height: 74px;
            margin: auto;
        }

        .louzir-brand {
            display: inline-flex;
            width: fit-content;
            flex-direction: column;
            align-items: center;
            color: var(--auth-ink);
            text-decoration: none;
            background: transparent;
        }

        .louzir-brand-logo {
            width: auto;
            max-width: 190px;
            max-height: 58px;
            object-fit: contain;
            background: transparent;
        }

        .louzir-brand-name {
            position: relative;
            font-family: "Poppins", "Segoe UI", sans-serif;
            font-size: 47px;
            font-weight: 600;
            line-height: .95;
        }

        .louzir-brand-flower {
            position: absolute;
            top: -11px;
            right: -22px;
            color: var(--auth-accent);
            font-size: 24px;
            line-height: 1;
            transform: rotate(20deg);
        }

        .louzir-brand-subtitle {
            margin-top: 8px;
            font-size: 9px;
            letter-spacing: 3.6px;
        }

        .louzir-header-caption {
            display: none;
            color: var(--auth-muted);
            font-size: 12px;
        }

        .louzir-auth-layout {
            display: grid;
            grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr);
            max-width: 1280px;
            min-height: 570px;
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid var(--auth-border);
            border-radius: 8px;
            background: var(--auth-card);
            box-shadow: var(--auth-shadow);
        }

        .louzir-editorial-panel {
            position: relative;
            min-width: 0;
            overflow: hidden;
            background: var(--auth-soft);
        }

        .louzir-editorial-panel::after {
            position: absolute;
            inset: 0;
            content: "";
            background:
                linear-gradient(180deg, rgba(250, 249, 247, .08) 0%, rgba(250, 249, 247, .1) 48%, rgba(250, 249, 247, .78) 100%),
                linear-gradient(90deg, rgba(250, 249, 247, .18) 0%, rgba(250, 249, 247, 0) 55%);
            pointer-events: none;
        }

        .louzir-editorial-panel img {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: 50% 45%;
        }

        .louzir-editorial-label {
            position: absolute;
            top: 34px;
            left: 34px;
            display: flex;
            align-items: center;
            gap: 13px;
            color: var(--auth-accent);
            font-size: 10px;
            letter-spacing: 2px;
            z-index: 1;
            text-shadow: 0 1px 12px rgba(250, 249, 247, .9);
        }

        .louzir-editorial-line {
            width: 27px;
            height: 1px;
            background: var(--auth-accent);
        }

        .louzir-editorial-copy {
            position: absolute;
            bottom: 58px;
            left: 38px;
            color: var(--auth-ink);
            z-index: 1;
            max-width: 300px;
            text-shadow: 0 1px 18px rgba(250, 249, 247, .95);
        }

        .louzir-editorial-eyebrow {
            color: var(--auth-accent);
            font-size: 9px;
            letter-spacing: 2.4px;
        }

        .louzir-editorial-copy h2 {
            margin: 14px 0 17px;
            font-family: "Poppins", "Segoe UI", sans-serif;
            font-size: 40px;
            font-weight: 600;
            line-height: 1.07;
        }

        .louzir-editorial-copy p {
            color: var(--auth-accent);
            font-size: 12px;
            line-height: 1.85;
        }

        .louzir-editorial-bottom {
            position: absolute;
            bottom: 18px;
            left: 38px;
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--auth-accent);
            font-size: 8px;
            letter-spacing: 2px;
            z-index: 1;
            text-shadow: 0 1px 12px rgba(250, 249, 247, .9);
        }

        .louzir-form-panel {
            display: flex;
            min-width: 0;
            flex-direction: column;
            align-items: center;
            padding: 14px 44px 18px;
        }

        .louzir-form-content {
            width: 100%;
            max-width: 480px;
        }

        .louzir-welcome-icon {
            display: none;
            width: 34px;
            height: 34px;
            margin-bottom: 12px;
            place-items: center;
            border: 1px solid var(--auth-border);
            border-radius: 50%;
            background: var(--auth-bg);
            color: var(--auth-accent);
            font-size: 18px;
        }

        .louzir-form-eyebrow {
            margin-bottom: 6px;
            color: var(--auth-accent);
            font-size: 9px;
            letter-spacing: 2px;
        }

        .louzir-form-title {
            font-family: "Poppins", "Segoe UI", sans-serif;
            font-size: 31px;
            font-weight: 600;
            line-height: 1.15;
        }

        .louzir-form-intro {
            margin-top: 8px;
            color: var(--auth-muted);
            font-size: 12px;
            line-height: 1.8;
        }

        .louzir-social-login {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 14px;
        }

        .louzir-social-login a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            height: 40px;
            border: 1px solid var(--auth-border);
            border-radius: 6px;
            background: var(--auth-card);
            color: var(--auth-ink);
            font-size: 12px;
            font-weight: 500;
            transition: background .15s ease, border-color .15s ease;
        }

        .louzir-social-login a:hover {
            border-color: var(--auth-accent);
            background: var(--auth-soft);
        }

        .louzir-social-login svg {
            width: 20px;
            height: 20px;
        }

        .louzir-form-divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 13px 0;
            color: var(--auth-muted);
            font-size: 10px;
        }

        .louzir-form-divider span {
            flex: 1;
            height: 1px;
            background: var(--auth-border);
        }

        .louzir-auth-form label {
            display: block;
            margin-bottom: 6px;
            color: var(--auth-ink);
            font-size: 11px;
            font-weight: 500;
        }

        .louzir-auth-form .control-group {
            margin-bottom: 10px;
        }

        .louzir-auth-form input:not([type="checkbox"]) {
            height: 38px;
            border-color: var(--auth-border);
            border-radius: 6px;
            background: var(--auth-card);
            box-shadow: none;
            color: var(--auth-ink);
            font-size: 12px;
        }

        .louzir-auth-form input:not([type="checkbox"]):focus {
            border-color: var(--auth-accent);
            box-shadow: 0 0 0 1px var(--auth-accent);
        }

        .louzir-password-group {
            position: relative;
        }

        .louzir-password-toggle {
            position: absolute;
            top: 29px;
            right: 10px;
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border-radius: 5px;
            color: var(--auth-muted);
            transition: color .15s ease, background .15s ease;
        }

        .louzir-password-toggle:hover,
        .louzir-password-toggle:focus-visible {
            background: var(--auth-soft);
            color: var(--auth-accent);
            outline: none;
        }

        .louzir-password-eye {
            width: 17px;
            height: 17px;
        }

        .louzir-password-input {
            padding-right: 45px !important;
        }

        .louzir-form-options {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 6px;
            margin-top: -1px;
            margin-bottom: 12px;
        }

        .louzir-checkbox-line {
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--auth-muted);
            font-size: 10px;
        }

        .louzir-checkbox-line input {
            width: 15px;
            height: 15px;
            margin: 0;
            border: 1.5px solid #cfc3ba;
            border-radius: 4px;
            background: #fff;
            accent-color: var(--auth-accent);
            flex-shrink: 0;
        }

        .louzir-terms-line {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 16px;
            color: var(--auth-muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .louzir-terms-line input {
            width: 15px;
            height: 15px;
            margin: 0;
            flex: 0 0 auto;
            border: 1.5px solid #cfc3ba;
            border-radius: 4px;
            background: #fff;
            accent-color: var(--auth-accent);
        }

        .louzir-terms-line label {
            margin: 0;
            color: var(--auth-muted);
            cursor: pointer;
            font-size: inherit;
        }

        .louzir-terms-line label:hover {
            color: var(--auth-accent);
        }

        .louzir-forgot-link {
            height: auto;
            padding: 0;
            color: var(--auth-accent);
            font-size: 10px;
            font-weight: 400;
            text-decoration: none;
        }

        .louzir-login-button {
            position: relative;
            width: 100%;
            height: 42px;
            border-radius: 6px;
            background: #000;
            color: #fff;
            font-size: 12px;
            font-weight: 500;
            transition: background .15s ease;
        }

        .louzir-login-button:hover {
            background: var(--auth-ink);
        }

        .louzir-security-note {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-top: 10px;
            color: var(--auth-muted);
            font-size: 10px;
        }

        .louzir-form-bottom {
            display: none;
            margin-top: auto;
            padding-top: 10px;
            color: var(--auth-accent);
            font-family: "Poppins", "Segoe UI", sans-serif;
            font-size: 15px;
            font-style: normal;
            text-align: center;
        }

        .louzir-auth-footer {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            max-width: 1280px;
            margin: auto;
            display: none;
            color: var(--auth-muted);
            font-size: 9px;
        }

        .louzir-auth-footer span:last-child {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .louzir-auth-link {
            color: var(--auth-accent);
            font-weight: 500;
        }

        .louzir-auth-switch {
            margin-top: 12px;
            text-align: center;
            color: var(--auth-muted);
            font-size: 12px;
            font-weight: 500;
        }

        .louzir-auth-alert {
            margin: 16px 0;
            padding: 12px 16px;
            border: 1px solid #f1b8b8;
            border-radius: 6px;
            background: #fff1f1;
            color: #a61b1b;
            font-size: 13px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        @media (min-width: 1500px) {
            .louzir-auth-layout {
                min-height: 590px;
            }

            .louzir-form-panel {
                padding-top: 59px;
            }
        }

        @media (max-width: 900px) {
            .louzir-auth-page {
                padding: 0 24px;
            }

            .louzir-form-panel {
                padding-right: 30px;
                padding-left: 30px;
            }

            .louzir-editorial-copy {
                left: 27px;
            }

            .louzir-editorial-copy h2 {
                font-size: 40px;
            }

            .louzir-form-title {
                font-size: 32px;
            }
        }

        @media (max-width: 640px) {
            .louzir-auth-page {
                padding: 0 20px;
            }

            .louzir-brand-header {
                grid-template-columns: 1fr;
                justify-items: center;
                height: 110px;
            }

            .louzir-brand-name {
                font-size: 43px;
            }

            .louzir-header-caption {
                display: none;
            }

            .louzir-auth-layout {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .louzir-editorial-panel {
                display: none;
            }

            .louzir-form-panel {
                padding: 32px 25px 26px;
            }

            .louzir-form-content {
                max-width: 380px;
            }

            .louzir-form-title {
                font-size: 32px;
            }

            .louzir-form-intro {
                font-size: 11px;
            }

            .louzir-auth-footer {
                grid-template-columns: 1fr;
                padding: 24px 0;
                text-align: center;
                font-size: 8px;
            }

            .louzir-auth-footer span:last-child {
                justify-content: center;
            }
        }
    </style>
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
                    src="{{ asset('assets/logo-auth-transparent.png') }}"
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
                                <x-shop::form.control-group.label class="required">
                                    @lang('shop::app.customers.login-form.email')
                                </x-shop::form.control-group.label>

                                <x-shop::form.control-group.control
                                    type="email"
                                    class="px-4 py-3"
                                    name="email"
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
                            <x-shop::form.control-group class="louzir-password-group">
                                <x-shop::form.control-group.label class="required">
                                    @lang('shop::app.customers.login-form.password')
                                </x-shop::form.control-group.label>

                                <x-shop::form.control-group.control
                                    type="password"
                                    class="louzir-password-input px-4 py-3"
                                    id="password"
                                    name="password"
                                    rules="required|min:6"
                                    value=""
                                    :label="trans('shop::app.customers.login-form.password')"
                                    :placeholder="trans('shop::app.customers.login-form.password')"
                                    :aria-label="trans('shop::app.customers.login-form.password')"
                                    aria-required="true"
                                />

                                <button
                                    type="button"
                                    class="louzir-password-toggle"
                                    aria-label="Afficher ou masquer le mot de passe"
                                    title="Afficher ou masquer le mot de passe"
                                    aria-pressed="false"
                                    onclick="switchVisibility(this)"
                                >
                                    <svg class="louzir-password-eye" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.7"/>
                                    </svg>
                                </button>

                                <x-shop::form.control-group.error control-name="password" />
                            </x-shop::form.control-group>

                            <div class="louzir-form-options">
                                @include('shop::customers.password-toggle')

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
                                <div class="louzir-terms-line">
                                    <input
                                        type="checkbox"
                                        name="agreement"
                                        id="login-agreement"
                                        value="1"
                                        required
                                    >

                                    <label for="login-agreement">
                                        {{ core()->getConfigData('general.gdpr.agreement.agreement_label') }}
                                    </label>
                                </div>

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
                        <span>Votre espace, en toute confidentialité.</span>
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

<<<<<<< HEAD
        <script>
            function switchVisibility(toggleButton) {
                let passwordField = document.getElementById("password");

                passwordField.type = passwordField.type === "password"
                    ? "text"
                    : "password";

                if (toggleButton) {
                    toggleButton.setAttribute('aria-pressed', passwordField.type === 'text' ? 'true' : 'false');
                    toggleButton.setAttribute('title', passwordField.type === 'text'
                        ? 'Masquer le mot de passe'
                        : 'Afficher le mot de passe');
                }
            }
        </script>
=======
>>>>>>> 0d593079a7fad1e2384c266c48ee3c8f940abffe
    @endpush
</x-shop::layouts>
