{!! view_render_event('bagisto.shop.settings.gdpr.modal.before') !!}

@pushOnce('styles')
    <style>
        .louzir-cookie-consent {
            width: min(35rem, calc(100vw - 1.25rem));
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1.25rem;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 1.25rem 1.25rem 1rem;
            color: #111827;
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.16);
        }

        .louzir-cookie-consent .cookie-title {
            color: #111827;
            font-size: 1.3rem;
            line-height: 1.25;
            letter-spacing: -0.03em;
            font-weight: 700;
        }

        .louzir-cookie-consent .cookie-description {
            color: #4b5563;
            font-size: 0.9rem;
            line-height: 1.65;
            margin: 0;
        }

        .louzir-cookie-consent .cookie-description a {
            color: #111827;
            text-decoration: underline;
            text-underline-offset: 0.2em;
            font-weight: 600;
        }

        .louzir-cookie-consent .cookie-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .louzir-cookie-consent button,
        .louzir-cookie-consent .cookie-customize {
            border-radius: 0.875rem;
            padding: 0.8rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 700;
            text-align: center;
            transition: all 0.2s ease;
        }

        .louzir-cookie-consent .cookie-accept {
            background: linear-gradient(135deg, #111827 0%, #374151 100%);
            color: #fff;
            border: none;
            box-shadow: 0 10px 24px rgba(17, 24, 39, 0.18);
        }

        .louzir-cookie-consent .cookie-reject,
        .louzir-cookie-consent .cookie-customize {
            border: 1px solid rgba(17, 24, 39, 0.12);
            background: rgba(255, 255, 255, 0.7);
            color: #111827;
        }

        .louzir-cookie-consent .cookie-customize {
            display: block;
            margin-top: 0.75rem;
            width: 100%;
        }

        .louzir-cookie-consent .cookie-accept:hover,
        .louzir-cookie-consent .cookie-reject:hover,
        .louzir-cookie-consent .cookie-customize:hover {
            transform: translateY(-1px);
        }

        @media (max-width: 640px) {
            .louzir-cookie-consent {
                width: min(100%, calc(100vw - 1rem));
                border-radius: 1rem;
                padding: 1rem;
            }

            .louzir-cookie-consent .cookie-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endPushOnce

<v-cookie></v-cookie>

{!! view_render_event('bagisto.shop.settings.gdpr.modal.before') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-cookie-template"
    >
        {!! view_render_event('bagisto.shop.settings.gdpr.modal.cookie.before') !!}

            @if (core()->getConfigData('general.gdpr.cookie.enabled'))
                <div
                    class="js-cookie-consent louzir-cookie-consent fixed z-[999] mx-4 box-border hidden min-h-5 overflow-hidden"
                    :class="getPositionClasses(position)"
                >
                    <div class="cookieTitle">
                        <span
                            class="cookie-title mb-1.5 block"
                            v-pre
                        >
                            {{ core()->getConfigData('general.gdpr.cookie.static_block_identifier') }}
                        </span>
                    </div>

                    <div class="cookieDesc cookie-consent__message">
                        <p
                            class="cookie-description mt-2.5 block"
                            v-pre
                        >
                            {{ core()->getConfigData('general.gdpr.cookie.description') }}

                            <a
                                href="{{ url('page/privacy-policy') }}"
                            >
                                @lang('shop::app.components.layouts.cookie.index.privacy-policy')
                            </a>
                        </p>
                    </div>

                    <div class="cookieButton">
                        <div class="cookie-actions">
                            <button
                                class="cookie-accept box-border inline-block w-full cursor-pointer"
                                @click="createCookie()"
                            >
                                @lang('shop::app.components.layouts.cookie.index.accept')
                            </button>

                            <button
                                class="cookie-reject box-border inline-block w-full cursor-pointer"
                                @click="rejectCookie()"
                            >
                                @lang('shop::app.components.layouts.cookie.index.reject')
                            </button>
                        </div>

                        <a
                            class="cookie-customize box-border w-full cursor-pointer"
                            href="{{ route('shop.customers.gdpr.cookie-consent') }}"
                        >
                            @lang('shop::app.components.layouts.cookie.index.learn-more-and-customize')
                        </a>
                    </div>
                </div>
            @endif

        {!! view_render_event('bagisto.shop.settings.gdpr.modal.cookie.before') !!}
    </script>

    <script type="module">
        const secureFlag = {!! json_encode(config('session.secure') ? ';secure' : '') !!};
        const sameSiteFlag = {!! json_encode(config('session.same_site') ? ';samesite=' . config('session.same_site') : '') !!};

        app.component('v-cookie', {
            template: '#v-cookie-template',

            data() {
                return {
                    cookieDomain: '{{ config('session.domain') ?? request()->getHost() }}',
                    cookieIp: "{{ request()->ip() }}",
                    position: "{{ core()->getConfigData('general.gdpr.cookie.position') ?? 'center' }}"
                };
            },

            mounted() {
                if (! this.cookieExists()) {
                    this.showCookieDialog();
                }
            },

            methods: {
                getPositionClasses(position) {
                    const positionClasses = {
                        'center': 'left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 max-w-[420px]',
                        'top-center': 'top-4 left-1/2 -translate-x-1/2 max-w-[420px]',
                        'bottom-center': 'bottom-4 left-1/2 -translate-x-1/2 max-w-[420px]',
                        'bottom-right': 'bottom-4 right-0 sm:max-w-[420px]',
                        'bottom-left': 'bottom-4 left-0 sm:max-w-[420px]',
                        'top-right': 'top-4 right-0 sm:max-w-[420px]',
                        'top-left': 'top-4 left-0 sm:max-w-[420px]',
                    };

                    return positionClasses[position] || positionClasses['center'];
                },

                createCookie() {
                    this.consentWithCookies();

                    this.acceptAllConsentWithCookies();

                    this.hideCookieDialog();
                },

                rejectCookie() {
                    this.hideCookieDialog();
                },

                setCookie(name, value, expirationInDays) {
                    const date = new Date();

                    date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));

                    document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/${secureFlag}${sameSiteFlag}`;
                },

                consentWithCookies() {
                    this.setCookie('cookie-consent', 1, 365 * 20);
                },

                acceptAllConsentWithCookies() {
                    this.setCookie('ip_address', this.cookieIp, 365 * 20);
                },

                cookieExists() {
                    return document.cookie.includes(`cookie-consent=${1}`);
                },

                hideCookieDialog() {
                    const cookieConsentElement = document.querySelector('.js-cookie-consent');

                    if (cookieConsentElement) {
                        cookieConsentElement.style.display = 'none';
                    }
                },

                showCookieDialog() {
                    const cookieConsentElement = document.querySelector('.js-cookie-consent');

                    if (cookieConsentElement) {
                        cookieConsentElement.style.display = 'block';
                    }
                },
            },
        });
    </script>
@endPushOnce
