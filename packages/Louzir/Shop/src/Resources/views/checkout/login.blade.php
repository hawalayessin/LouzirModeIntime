<!-- Checkout Login Vue JS Component -->
<v-checkout-login>
    <div class="flex items-center">
        <span class="cursor-pointer text-base font-medium text-blue-700">
            @lang('shop::app.checkout.login.title')
        </span>
    </div>
</v-checkout-login>

@pushOnce('scripts')
    {!! \Webkul\Customer\Facades\Captcha::renderJS() !!}

    <script
        type="text/x-template"
        id="v-checkout-login-template"
    >
        <div>
            <div class="flex items-center">
                <span
                    class="cursor-pointer text-base font-medium text-blue-700"
                    role="button"
                    @click="$refs.loginModel.open()"
                >
                    @lang('shop::app.checkout.login.title')
                </span>
            </div>

            <!-- Login Form -->
            <x-shop::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                {!! view_render_event('bagisto.shop.checkout.login.before') !!}

                <!-- Login form -->
                <form @submit="handleSubmit($event, login)">
                    {!! view_render_event('bagisto.shop.checkout.login.form_controls.before') !!}

                    <!-- Login modal -->
                    <x-shop::modal ref="loginModel">
                        <!-- Modal Header -->
                        <x-slot:header class="louzir-checkout-login-header">
                            <h2 class="text-2xl font-medium max-md:text-base">
                                @lang('shop::app.checkout.login.title')
                            </h2>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content class="louzir-checkout-login-content">
                            @if (core()->getConfigData('customer.settings.social_login.enable_google'))
                                <a
                                    href="{{ route('customer.social-login.index', 'google') }}"
                                    class="louzir-checkout-google"
                                    aria-label="Google"
                                >
                                    <span class="louzir-checkout-google-icon">
                                        @include('social_login::icons.google')
                                    </span>

                                    <span>
                                        {{ app()->getLocale() === 'fr' ? 'Continuer avec Google' : (app()->getLocale() === 'ar' ? 'المتابعة باستخدام Google' : 'Continue with Google') }}
                                    </span>
                                </a>

                                <div class="louzir-checkout-divider">
                                    <span></span>
                                    {{ app()->getLocale() === 'fr' ? 'ou avec votre e-mail' : (app()->getLocale() === 'ar' ? 'أو باستخدام بريدك الإلكتروني' : 'or with your email') }}
                                    <span></span>
                                </div>
                            @endif

                            <!-- Email -->
                            <x-shop::form.control-group>
                                <x-shop::form.control-group.label class="required">
                                    @lang('shop::app.checkout.login.email')
                                </x-shop::form.control-group.label>

                                <x-shop::form.control-group.control
                                    type="email"
                                    class="px-6 py-4"
                                    name="email"
                                    rules="required|email"
                                    :label="trans('shop::app.checkout.login.email')"
                                    placeholder="email@example.com"
                                    :aria-label="trans('shop::app.checkout.login.email')"
                                    aria-required="true"
                                />

                                <x-shop::form.control-group.error control-name="email" />
                            </x-shop::form.control-group>

                            <!-- Password -->
                            <x-shop::form.control-group class="!mb-0">
                                <x-shop::form.control-group.label class="required">
                                    @lang('shop::app.checkout.login.password')
                                </x-shop::form.control-group.label>

                                <x-shop::form.control-group.control
                                    type="password"
                                    class="px-6 py-4"
                                    id="password"
                                    name="password"
                                    rules="required|min:6"
                                    :label="trans('shop::app.checkout.login.password')"
                                    :placeholder="trans('shop::app.checkout.login.password')"
                                    :aria-label="trans('shop::app.checkout.login.password')"
                                    aria-required="true"
                                />

                                <x-shop::form.control-group.error control-name="password" />
                            </x-shop::form.control-group>

                            <!-- Captcha -->
                            @if (core()->getConfigData('customer.captcha.credentials.status'))
                                <x-shop::form.control-group class="mt-5">
                                    {!! \Webkul\Customer\Facades\Captcha::render() !!}

                                    <x-shop::form.control-group.error control-name="recaptcha_token" />
                                </x-shop::form.control-group>
                            @endif
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer class="louzir-checkout-login-footer">
                            <div class="flex flex-wrap items-center gap-4">
                                <x-shop::button
                                    class="louzir-checkout-login-button primary-button max-w-none flex-auto rounded-sm px-11 py-3 max-md:rounded-lg max-md:py-1.5"
                                    :title="trans('shop::app.checkout.login.title')"
                                    ::loading="isStoring"
                                    ::disabled="isStoring"
                                />
                            </div>
                        </x-slot>
                    </x-shop::modal>

                    {!! view_render_event('bagisto.shop.checkout.login.form_controls.after') !!}
                </form>
            </x-shop::form>

            {!! view_render_event('bagisto.shop.checkout.login.after') !!}
        </div>
    </script>

    <script type="module">
        app.component('v-checkout-login', {
            template: '#v-checkout-login-template',

            data() {
                return {
                    isStoring: false,
                }
            },

            methods: {
                login(params, {
                    resetForm,
                    setErrors
                }) {
                    this.isStoring = true;

                    const captchaResponse = document.querySelector('[name="recaptcha_token"]')?.value

                    params['recaptcha_token'] = captchaResponse;

                    this.$axios.post("{{ route('shop.api.customers.session.create') }}", params)
                        .then((response) => {
                            this.isStoring = false;

                            window.location.reload();
                        })
                        .catch((error) => {
                            this.isStoring = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);

                                return;
                            }

                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response.data.message
                            });
                        });
                },
            }
        })
    </script>
@endPushOnce

@pushOnce('styles')
    <style>
        .louzir-checkout-login-header {
            border-bottom: 1px solid #e4ded7 !important;
            background: #fffaf6 !important;
            padding: 1.25rem 1.5rem !important;
        }

        .louzir-checkout-login-content {
            background: #fffaf6 !important;
            padding: 1.5rem !important;
        }

        .louzir-checkout-login-footer {
            margin-top: 0 !important;
            background: #fffaf6 !important;
            padding: 0 1.5rem 1.5rem !important;
        }

        .louzir-checkout-google {
            display: flex;
            min-height: 3.25rem;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            border: 1px solid #ded8d1;
            border-radius: 0.35rem;
            background: #fff;
            color: #111;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 150ms ease, border-color 150ms ease;
        }

        .louzir-checkout-google:hover {
            border-color: #111;
            background: #faf7f3;
        }

        .louzir-checkout-google-icon {
            display: inline-flex;
            width: 1.25rem;
            height: 1.25rem;
            align-items: center;
            justify-content: center;
        }

        .louzir-checkout-google-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .louzir-checkout-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.25rem 0;
            color: #8c7770;
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .louzir-checkout-divider span {
            height: 1px;
            flex: 1;
            background: #ded8d1;
        }

        .louzir-checkout-login-button {
            width: 100%;
            border-radius: 0.25rem !important;
            background: #111 !important;
            color: #fff !important;
            padding: 0.85rem 1rem !important;
        }
    </style>
@endPushOnce
