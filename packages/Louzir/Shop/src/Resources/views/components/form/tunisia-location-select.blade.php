@pushOnce('styles')
    <style>
        .tn-location-select { position: relative; }
        .tn-location-select__input {
            width: 100%; min-height: 48px; padding: 12px 42px 12px 16px;
            border: 1px solid #d1d5db; border-radius: 12px; background: #fff;
            color: #374151; font: inherit; font-size: 14px; line-height: 1.5;
            text-overflow: ellipsis; box-shadow: 0 1px 2px rgb(0 0 0 / 4%);
        }
        .tn-location-select__input:focus { outline: 2px solid #6b7280; outline-offset: 2px; }
        .tn-location-select__input[aria-invalid="true"] { border-color: #ef4444; }
        .tn-location-select__input:disabled { background: #f3f4f6; color: #6b7280; cursor: not-allowed; }
        .tn-location-select__chevron { position: absolute; inset-inline-end: 16px; top: 17px; width: 16px; height: 16px; color: #4b5563; pointer-events: none; }
        .tn-location-menu {
            position: fixed; z-index: 1000; overflow-y: auto; overscroll-behavior: contain;
            box-sizing: border-box; padding: 6px; border: 1px solid #d1d5db;
            border-radius: 12px; background: #fff; color: #374151;
            box-shadow: 0 12px 28px -8px rgb(17 24 39 / 24%);
            font-family: "Poppins", "Segoe UI", sans-serif; font-size: 14px;
            scrollbar-width: thin; scrollbar-color: #9ca3af #f9fafb;
        }
        .tn-location-menu__option {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            min-height: 40px; padding: 9px 12px; border-radius: 7px;
            cursor: pointer; line-height: 1.5; overflow-wrap: anywhere;
        }
        .tn-location-menu__option[data-active="true"] { background: #f3f4f6; color: #111827; }
        .tn-location-menu__option[aria-selected="true"] { color: #111827; font-weight: 600; }
        .tn-location-menu__check { flex: 0 0 16px; width: 16px; height: 16px; }
        .tn-location-menu__empty { padding: 12px; color: #6b7280; line-height: 1.5; }
        [dir="rtl"] .tn-location-select__input { padding: 12px 16px 12px 42px; }
        @media (forced-colors: active) {
            .tn-location-select__input, .tn-location-menu { border-color: ButtonText; }
            .tn-location-menu__option[data-active="true"] { outline: 1px solid Highlight; }
        }
    </style>
@endPushOnce

@pushOnce('scripts')
    <script type="text/x-template" id="v-tunisia-location-select-template">
        <div ref="control" class="tn-location-select">
            <input
                ref="input" :id="id" class="tn-location-select__input" type="text"
                role="combobox" aria-autocomplete="list" aria-haspopup="listbox"
                :aria-expanded="isOpen" :aria-controls="id + '-listbox'"
                :aria-activedescendant="isOpen && activeIndex >= 0 && filteredOptions[activeIndex] ? id + '-option-' + activeIndex : undefined"
                :aria-invalid="invalid" aria-required="true" :disabled="disabled"
                :value="isOpen ? query : selectedLabel"
                :placeholder="isOpen ? @js(trans('shop::tunisia.search')) : placeholder"
                autocomplete="off" spellcheck="false"
                @focus="openMenu" @click="openMenu" @input="search($event.target.value)"
                @keydown="onKeydown" @blur="closeMenu(); $emit('blur', $event)"
            >
            <svg class="tn-location-select__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <teleport to="body">
                <div v-if="isOpen" ref="menu" class="tn-location-menu" :style="menuStyle" :dir="direction" @mousedown.prevent>
                    <ul ref="listbox" :id="id + '-listbox'" role="listbox" :aria-label="placeholder">
                        <li
                            v-for="(option, index) in filteredOptions" :key="option.value"
                            :id="id + '-option-' + index" role="option"
                            class="tn-location-menu__option" :data-option-index="index"
                            :aria-selected="option.value === modelValue" :data-active="index === activeIndex"
                            @mouseenter="activeIndex = index" @click="select(option)"
                        >
                            <span>@{{ option.label }}</span>
                            <svg v-if="option.value === modelValue" class="tn-location-menu__check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </li>
                    </ul>
                    <p v-if="!filteredOptions.length" role="status" class="tn-location-menu__empty">@lang('shop::tunisia.no-results')</p>
                </div>
            </teleport>
        </div>
    </script>

    <script type="module">
        app.component('v-tunisia-location-select', {
            template: '#v-tunisia-location-select-template',
            props: {
                id: { type: String, required: true },
                modelValue: { type: String, default: '' },
                options: { type: Array, required: true },
                placeholder: { type: String, required: true },
                disabled: { type: Boolean, default: false },
                invalid: { type: Boolean, default: false },
            },
            emits: ['update:modelValue', 'blur'],
            data() {
                return { isOpen: false, query: '', activeIndex: -1, menuStyle: {}, direction: document.documentElement.dir || 'ltr' };
            },
            computed: {
                selectedLabel() {
                    return this.options.find(option => option.value === this.modelValue)?.label || '';
                },
                filteredOptions() {
                    const query = this.normalize(this.query);
                    return query ? this.options.filter(option => this.normalize(option.label).includes(query)) : this.options;
                },
            },
            watch: {
                disabled(value) { if (value) this.closeMenu(); },
                options() { this.closeMenu(); },
            },
            mounted() {
                document.addEventListener('pointerdown', this.onOutsidePointer);
                window.addEventListener('resize', this.positionMenu);
                window.addEventListener('scroll', this.positionMenu, true);
            },
            beforeUnmount() {
                document.removeEventListener('pointerdown', this.onOutsidePointer);
                window.removeEventListener('resize', this.positionMenu);
                window.removeEventListener('scroll', this.positionMenu, true);
            },
            methods: {
                normalize(value) {
                    return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase().trim();
                },
                openMenu() {
                    if (this.disabled || this.isOpen) return;
                    this.query = '';
                    this.isOpen = true;
                    this.activeIndex = Math.max(0, this.options.findIndex(option => option.value === this.modelValue));
                    this.positionMenu();
                    this.scrollToActive();
                },
                closeMenu() { this.isOpen = false; this.query = ''; this.activeIndex = -1; },
                search(value) {
                    this.query = value;
                    this.isOpen = true;
                    this.activeIndex = this.filteredOptions.length ? 0 : -1;
                    this.positionMenu();
                    this.scrollToActive();
                },
                select(option) {
                    this.$emit('update:modelValue', option.value);
                    this.closeMenu();
                },
                onOutsidePointer(event) {
                    if (!this.$refs.control?.contains(event.target) && !this.$refs.menu?.contains(event.target)) this.closeMenu();
                },
                positionMenu() {
                    if (!this.isOpen) return;
                    const rect = this.$refs.input.getBoundingClientRect();
                    if (rect.bottom <= 0 || rect.top >= window.innerHeight) { this.closeMenu(); return; }
                    const below = window.innerHeight - rect.bottom - 12;
                    const above = rect.top - 12;
                    const upwards = below < 180 && above > below;
                    this.menuStyle = {
                        left: Math.max(8, Math.min(rect.left, window.innerWidth - rect.width - 8)) + 'px',
                        width: Math.min(rect.width, window.innerWidth - 16) + 'px',
                        top: upwards ? 'auto' : rect.bottom + 6 + 'px',
                        bottom: upwards ? window.innerHeight - rect.top + 6 + 'px' : 'auto',
                        maxHeight: Math.max(48, Math.min(240, upwards ? above : below)) + 'px',
                    };
                },
                scrollToActive() {
                    this.$nextTick(() => this.$refs.listbox?.querySelector('[data-option-index="' + this.activeIndex + '"]')?.scrollIntoView({ block: 'nearest' }));
                },
                onKeydown(event) {
                    if (event.key === 'Escape') { event.preventDefault(); this.closeMenu(); return; }
                    if (event.key === 'Tab') { this.closeMenu(); return; }
                    if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
                        if (!this.isOpen && ['Home', 'End'].includes(event.key)) return;
                        event.preventDefault();
                        if (!this.isOpen) { this.openMenu(); return; }
                        const last = this.filteredOptions.length - 1;
                        this.activeIndex = event.key === 'Home' ? 0 : event.key === 'End' ? last
                            : Math.max(0, Math.min(last, this.activeIndex + (event.key === 'ArrowDown' ? 1 : -1)));
                        this.scrollToActive();
                    } else if (event.key === 'Enter' && this.isOpen) {
                        event.preventDefault();
                        const option = this.filteredOptions[this.activeIndex];
                        if (option) this.select(option);
                    }
                },
            },
        });
    </script>
@endPushOnce
