<v-auth-password-toggle controls="{{ $controls ?? 'password' }}"></v-auth-password-toggle>

@pushOnce('scripts')
    <script type="text/x-template" id="v-auth-password-toggle-template">
        <label class="louzir-checkbox-line">
            <input
                type="checkbox"
                id="show-password"
                :aria-controls="controls"
                :checked="visible"
                @change="toggle($event.target.checked)"
            >

            <span>@lang('shop::app.customers.login-form.show-password')</span>
        </label>
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
