<!-- SEO Meta Content -->
@push('meta')
    <meta
        name="description"
        content="@lang('shop::app.customers.signup-form.page-title')"
    />

    <meta
        name="keywords"
        content="@lang('shop::app.customers.signup-form.page-title')"
    />
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
        @lang('shop::app.customers.signup-form.page-title')
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
                aria-label="@lang('shop::app.customers.signup-form.bagisto')"
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

        <main class="louzir-auth-layout louzir-register-layout">
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
                class="louzir-form-panel louzir-register-panel"
                aria-labelledby="signup-title"
            >
                <div class="louzir-form-content">
                    <p class="louzir-form-eyebrow">@lang('shop::app.customers.signup-form.bagisto')</p>

                    <div class="louzir-form-heading">
                        <h1
                            id="signup-title"
                            class="louzir-form-title"
                        >
                            @lang('shop::app.customers.signup-form.page-title')
                        </h1>

                    </div>

                    <p class="louzir-form-intro">
                        @lang('shop::app.customers.signup-form.form-signup-text')
                    </p>

                    @if ($hasSocialLogin)
                        {!! view_render_event('bagisto.shop.customers.login_form_controls.after') !!}

                        <div class="louzir-form-divider">
                            <span></span>
                            {{ app()->getLocale() === 'fr' ? 'ou avec votre e-mail' : 'or with your email' }}
                            <span></span>
                        </div>
                    @endif

                    <div class="louzir-auth-form">
                        <x-shop::form :action="route('shop.customers.register.store')">
                            {!! view_render_event('bagisto.shop.customers.signup_form_controls.before') !!}

                            <div class="louzir-name-grid">
                                <!-- First Name -->
                                <x-shop::form.control-group>
                                    <x-shop::form.control-group.label class="required" for="first_name">
                                        @lang('shop::app.customers.signup-form.first-name')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="text"
                                        class="px-4 py-3"
                                        id="first_name"
                                        name="first_name"
                                        autocomplete="given-name"
                                        rules="required"
                                        :value="old('first_name')"
                                        :label="trans('shop::app.customers.signup-form.first-name')"
                                        :placeholder="trans('shop::app.customers.signup-form.first-name')"
                                        :aria-label="trans('shop::app.customers.signup-form.first-name')"
                                        aria-required="true"
                                    />

                                    <x-shop::form.control-group.error control-name="first_name" />
                                </x-shop::form.control-group>

                                {!! view_render_event('bagisto.shop.customers.signup_form.first_name.after') !!}

                                <!-- Last Name -->
                                <x-shop::form.control-group>
                                    <x-shop::form.control-group.label class="required" for="last_name">
                                        @lang('shop::app.customers.signup-form.last-name')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="text"
                                        class="px-4 py-3"
                                        id="last_name"
                                        name="last_name"
                                        autocomplete="family-name"
                                        rules="required"
                                        :value="old('last_name')"
                                        :label="trans('shop::app.customers.signup-form.last-name')"
                                        :placeholder="trans('shop::app.customers.signup-form.last-name')"
                                        :aria-label="trans('shop::app.customers.signup-form.last-name')"
                                        aria-required="true"
                                    />

                                    <x-shop::form.control-group.error control-name="last_name" />
                                </x-shop::form.control-group>
                            </div>

                            {!! view_render_event('bagisto.shop.customers.signup_form.last_name.after') !!}

                            <!-- Email -->
                            <x-shop::form.control-group>
                                <x-shop::form.control-group.label class="required" for="email">
                                    @lang('shop::app.customers.signup-form.email')
                                </x-shop::form.control-group.label>

                                <x-shop::form.control-group.control
                                    type="email"
                                    class="px-4 py-3"
                                    id="email"
                                    name="email"
                                    autocomplete="email"
                                    rules="required|email"
                                    :value="old('email')"
                                    :label="trans('shop::app.customers.signup-form.email')"
                                    placeholder="vous@exemple.com"
                                    :aria-label="trans('shop::app.customers.signup-form.email')"
                                    aria-required="true"
                                />

                                <x-shop::form.control-group.error control-name="email" />
                            </x-shop::form.control-group>

                            {!! view_render_event('bagisto.shop.customers.signup_form.email.after') !!}

                            <!-- Password -->
                            <x-shop::form.control-group>
                                <div class="louzir-password-label">
                                    <x-shop::form.control-group.label class="required" for="password">
                                        @lang('shop::app.customers.signup-form.password')
                                    </x-shop::form.control-group.label>

                                    @include('shop::customers.password-toggle')
                                </div>

                                <x-shop::form.control-group.control
                                    type="password"
                                    class="px-4 py-3"
                                    id="password"
                                    name="password"
                                    autocomplete="new-password"
                                    rules="required|min:6"
                                    :value="old('password')"
                                    :label="trans('shop::app.customers.signup-form.password')"
                                    :placeholder="trans('shop::app.customers.signup-form.password')"
                                    ref="password"
                                    :aria-label="trans('shop::app.customers.signup-form.password')"
                                    aria-required="true"
                                />

                                <x-shop::form.control-group.error control-name="password" />
                            </x-shop::form.control-group>

                            {!! view_render_event('bagisto.shop.customers.signup_form.password.after') !!}

                            <!-- Confirm Password -->
                            <x-shop::form.control-group>
                                <div class="louzir-password-label">
                                    <x-shop::form.control-group.label for="password_confirmation">
                                        @lang('shop::app.customers.signup-form.confirm-pass')
                                    </x-shop::form.control-group.label>

                                    @include('shop::customers.password-toggle', ['controls' => 'password_confirmation'])
                                </div>

                                <x-shop::form.control-group.control
                                    type="password"
                                    class="px-4 py-3"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    autocomplete="new-password"
                                    rules="confirmed:@password"
                                    value=""
                                    :label="trans('shop::app.customers.signup-form.password')"
                                    :placeholder="trans('shop::app.customers.signup-form.confirm-pass')"
                                    :aria-label="trans('shop::app.customers.signup-form.confirm-pass')"
                                    aria-required="true"
                                />

                                <x-shop::form.control-group.error control-name="password_confirmation" />
                            </x-shop::form.control-group>

                            {!! view_render_event('bagisto.shop.customers.signup_form.password_confirmation.after') !!}

                            <!-- Captcha -->
                            @if (core()->getConfigData('customer.captcha.credentials.status'))
                                <x-shop::form.control-group>
                                    {!! \Webkul\Customer\Facades\Captcha::render() !!}

                                    <x-shop::form.control-group.error control-name="recaptcha_token" />
                                </x-shop::form.control-group>
                            @endif

                            <!-- Subscribed Button -->
                            @if (core()->getConfigData('customer.settings.create_new_account_options.news_letter'))
                                <label class="louzir-checkbox-line">
                                    <input
                                        type="checkbox"
                                        name="is_subscribed"
                                        id="is-subscribed"
                                    >

                                    @lang('shop::app.customers.signup-form.subscribe-to-newsletter')
                                </label>
                            @endif

                            {!! view_render_event('bagisto.shop.customers.signup_form.newsletter_subscription.after') !!}

                            @if(
                                core()->getConfigData('general.gdpr.settings.enabled')
                                && core()->getConfigData('general.gdpr.agreement.enabled')
                            )
                                @include('shop::customers.agreement')

                                @if (core()->getConfigData('general.gdpr.agreement.agreement_content'))
                                    <button
                                        type="button"
                                        class="louzir-terms-link"
                                        @click="$refs.termsModal.open()"
                                    >
                                        @lang('shop::app.customers.signup-form.click-here')
                                    </button>
                                @endif

                                <x-shop::form.control-group.error control-name="agreement" />
                            @endif

                            <button
                                class="louzir-login-button"
                                type="submit"
                            >
                                @lang('shop::app.customers.signup-form.button-title')
                            </button>

                            <div class="louzir-auth-switch">
                                <p>@lang('shop::app.customers.signup-form.account-exists')</p>

                                <a
                                    href="{{ route('shop.customer.session.index') }}"
                                    class="louzir-login-return"
                                >
                                    @lang('shop::app.customers.signup-form.sign-in-button')
                                </a>
                            </div>

                            {!! view_render_event('bagisto.shop.customers.signup_form_controls.after') !!}
                        </x-shop::form>
                    </div>
                </div>

                <div class="louzir-form-bottom">
                    <span>Une touche de douceur, chaque jour.</span>
                </div>

            </section>
        </main>

        <footer class="louzir-auth-footer">
            <span>@lang('shop::app.customers.signup-form.footer', ['current_year'=> date('Y') ])</span>

            <span>
                <span>*</span>
                Espace personnel
            </span>
        </footer>
    </div>

    @push('scripts')
        {!! \Webkul\Customer\Facades\Captcha::renderJS() !!}
    @endpush

    <!-- Terms & Conditions Modal -->
    <x-shop::modal ref="termsModal">
        <x-slot:toggle></x-slot>

        <x-slot:header class="!p-5">
            <p>@lang('shop::app.customers.signup-form.terms-conditions')</p>
        </x-slot>

        <x-slot:content class="!p-5">
            <div class="max-h-[500px] overflow-auto">
                {!! core()->getConfigData('general.gdpr.agreement.agreement_content') !!}
            </div>
        </x-slot>
    </x-shop::modal>
</x-shop::layouts>
