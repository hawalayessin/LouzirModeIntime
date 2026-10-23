<v-auth-password-toggle controls="{{ $controls ?? 'password' }}"></v-auth-password-toggle>

@pushOnce('scripts')
    <script type="text/x-template" id="v-auth-password-toggle-template">
        <button
            type="button"
            class="louzir-password-eye"
            :aria-controls="controls"
            :aria-pressed="visible"
            :aria-label="visible ? @js(trans('shop::auth.hide-password')) : @js(trans('shop::auth.show-password'))"
            :title="visible ? @js(trans('shop::auth.hide-password')) : @js(trans('shop::auth.show-password'))"
            @click="toggle(!visible)"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                <circle cx="12" cy="12" r="3" />
                <path v-if="visible" d="m3 3 18 18" />
            </svg>
        </button>
    </script>

    <script type="module">
        app.component('v-auth-password-toggle', {
            template: '#v-auth-password-toggle-template',

            props: {
                controls: { type: String, default: 'password' },
            },

            data() {
                return { visible: false };
            },

            methods: {
                toggle(visible) {
                    this.visible = visible;
                    const ids = this.controls.split(/\s+/);

                    for (const field of this.$el.closest('form')?.querySelectorAll('input') ?? []) {
                        if (ids.includes(field.id)) {
                            field.type = visible ? 'text' : 'password';
                        }
                    }
                },
            },
        });
    </script>
@endPushOnce
