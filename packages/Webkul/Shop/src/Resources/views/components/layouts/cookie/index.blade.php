{!! view_render_event('bagisto.shop.settings.gdpr.modal.before') !!}


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
