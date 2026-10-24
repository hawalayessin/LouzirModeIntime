@pushOnce('scripts')
    <script type="text/x-template" id="v-category-branch-template">
        <li :class="{ 'louzir-category-root': level === 0 }">
            <div class="louzir-category-row" :style="{ paddingInlineStart: (20 + level * 16) + 'px' }">
                <a :href="category.url">@{{ category.name }}</a>
                <button v-if="category.children?.length" type="button" class="louzir-category-branch-toggle"
                    :aria-label="category.name" :aria-expanded="expanded" :aria-controls="childrenId"
                    @click="expanded = !expanded">
                    <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                </button>
            </div>
            <ul v-if="category.children?.length" v-show="expanded" :id="childrenId" class="louzir-category-children">
                <v-category-branch v-for="child in category.children" :key="child.id" :category="child" :level="level + 1" :prefix="prefix" :query="query" />
            </ul>
        </li>
    </script>
    <script type="text/x-template" id="v-category-browser-template">
        <nav class="louzir-category-browser" aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.categories')">
            <div class="louzir-category-search">
                <label class="sr-only" :for="'category-search-' + prefix">@lang('shop::home.category-search')</label>
                <div class="louzir-category-search-field">
                    <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/></svg>
                    <input type="search" :id="'category-search-' + prefix" v-model="query" maxlength="120" autocomplete="off"
                        placeholder="@lang('shop::home.category-search')" @keydown.esc.prevent.stop="query = ''">
                    <button v-if="query" type="button" @click="query = ''" aria-label="@lang('shop::home.category-search-clear')">
                        <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m6 6 12 12M6 18 18 6"/></svg>
                    </button>
                </div>
            </div>
            <ul class="louzir-category-tree" :data-category-menu="prefix">
                <v-category-branch v-for="category in filteredCategories" :key="category.id" :category="category" :prefix="prefix" :query="query.trim()" />
            </ul>
            <p v-if="!loading && !filteredCategories.length" class="louzir-category-empty" role="status">@lang('shop::home.category-search-empty')</p>
        </nav>
    </script>
    <script type="module">
        const normalizeCategoryName = value => String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase().trim();

        app.component('v-category-browser', {
            template: '#v-category-browser-template',
            props: {
                categories: { type: Array, required: true },
                loading: { type: Boolean, default: false },
                prefix: { type: String, required: true },
            },
            data() { return { query: '' }; },
            computed: {
                filteredCategories() {
                    const needle = normalizeCategoryName(this.query);
                    if (!needle) return this.categories;
                    const filter = categories => categories.flatMap(category => {
                        if (normalizeCategoryName(category.name).includes(needle)) return [category];
                        const children = filter(category.children || []);
                        return children.length ? [{ ...category, children }] : [];
                    });
                    return filter(this.categories);
                },
            },
        });

        app.component('v-category-branch', {
            template: '#v-category-branch-template',
            props: {
                category: { type: Object, required: true },
                prefix: { type: String, required: true },
                level: { type: Number, default: 0 },
                query: { type: String, default: '' },
            },
            data() { return { expanded: Boolean(this.query) }; },
            watch: {
                query(value) { this.expanded = Boolean(value); },
            },
            computed: {
                childrenId() { return 'category-children-' + this.prefix + '-' + this.category.id; },
            },
        });
        const initializeCategoryPanelPosition = () => {
            const navbar = document.querySelector('.louzir-header');
            if (!navbar) return;
            let frame;
            const update = () => {
                cancelAnimationFrame(frame);
                frame = requestAnimationFrame(() => {
                    document.documentElement.style.setProperty('--louzir-navbar-bottom', Math.max(0, navbar.getBoundingClientRect().bottom) + 'px');
                });
            };
            if ('ResizeObserver' in window) new ResizeObserver(update).observe(navbar);
            window.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update, { passive: true });
            update();
        };
        if (document.readyState === 'complete') initializeCategoryPanelPosition();
        else window.addEventListener('load', initializeCategoryPanelPosition, { once: true });
    </script>
@endPushOnce
