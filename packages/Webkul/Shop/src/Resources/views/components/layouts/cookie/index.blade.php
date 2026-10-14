{!! view_render_event('bagisto.shop.settings.gdpr.modal.before') !!}

<style>
        .louzir-cookie-consent {
            width: min(30rem, calc(100vw - 2rem));
            border: 1px solid #ded1c6;
            border-radius: 0.75rem;
            background: #fffaf6;
            padding: 1.35rem;
            color: #171717;
            box-shadow: 0 20px 55px rgb(49 38 31 / 24%);
            font-family: "Poppins", "Segoe UI", sans-serif;
        }

        .louzir-cookie-consent .cookie-title {
            color: #171717;
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 1.35rem;
            line-height: 1.2;
        }

        .louzir-cookie-consent .cookieTitle {
            margin-bottom: .45rem;
        }

        .louzir-cookie-consent .cookie-description {
            color: #665b55;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .louzir-cookie-consent .cookie-description a {
            color: #171717;
            text-decoration: underline;
        }

        .louzir-cookie-consent .cookie-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .louzir-cookie-consent button,
        .louzir-cookie-consent .cookie-customize {
            border: 1px solid transparent;
            border-radius: 0.25rem;
            padding: 0.7rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
        }

        .louzir-cookie-consent button:hover,
        .louzir-cookie-consent .cookie-customize:hover {
            border-color: #79573d;
        }

        .louzir-cookie-consent button:focus-visible,
        .louzir-cookie-consent a:focus-visible {
            outline: 2px solid #79573d;
            outline-offset: 2px;
        }

        .louzir-cookie-consent .cookie-accept {
            background: #171717;
            color: #fff;
        }

        .louzir-cookie-consent .cookie-reject,
        .louzir-cookie-consent .cookie-customize {
            border: 1px solid #cfc3ba;
            background: transparent;
            color: #171717;
        }

        .louzir-cookie-consent .cookie-customize {
            display: block;
            margin-top: 0.5rem;
        }

        @media (max-width: 480px) {
            .louzir-cookie-consent {
                width: calc(100vw - 1rem);
                padding: 1rem;
            }

            .louzir-cookie-consent .cookie-actions {
                grid-template-columns: 1fr;
            }
        }
</style>

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
                    style="width:min(20rem, calc(100vw - 1.5rem)); border:1px solid #ded1c6; border-radius:1rem; background:#fffaf6; padding:1rem; color:#171717; box-shadow:0 16px 38px rgb(49 38 31 / 20%); font-family:Poppins, 'Segoe UI', sans-serif;"
                >
                    <div class="cookieTitle">
                        <span
                            class="cookie-title mb-1.5 block"
                            v-pre
                            style="display:block; margin-bottom:.4rem; color:#231f1c; font-family:'DM Serif Display', Georgia, serif; font-size:1.05rem; font-weight:600; line-height:1.2;"
                        >
                            Accepter les cookies
                        </span>
                    </div>

                    <div class="cookieDesc cookie-consent__message">
                        <p
                            class="cookie-description mt-2.5 block"
                            v-pre
                            style="margin:.35rem 0 .8rem; color:#665b55; font-size:.75rem; line-height:1.5;"
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
                        <div class="cookie-actions" style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.4rem; margin-top:.7rem;">
                            <button
                                class="cookie-accept box-border inline-block w-full cursor-pointer"
                                @click="createCookie()"
                                style="min-height:2.15rem; border:1px solid #231f1c; border-radius:.4rem; background:#231f1c; color:#fff; padding:.45rem .55rem; font-size:.7rem; font-weight:600;"
                            >
                                @lang('shop::app.components.layouts.cookie.index.accept')
                            </button>

                            <button
                                class="cookie-reject box-border inline-block w-full cursor-pointer"
                                @click="rejectCookie()"
                                style="min-height:2.15rem; border:1px solid #cfc3ba; border-radius:.4rem; background:transparent; color:#231f1c; padding:.45rem .55rem; font-size:.7rem; font-weight:600;"
                            >
                                @lang('shop::app.components.layouts.cookie.index.reject')
                            </button>
                        </div>

                        <a
                            class="cookie-customize box-border w-full cursor-pointer"
                            href="{{ route('shop.customers.gdpr.cookie-consent') }}"
                            style="display:block; margin-top:.55rem; color:#79573d; font-size:.68rem; text-align:center; text-decoration:underline; text-underline-offset:2px;"
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
