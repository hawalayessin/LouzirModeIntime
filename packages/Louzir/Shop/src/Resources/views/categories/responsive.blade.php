{{-- Shared by category and search so the filter drawer follows viewport changes. --}}
mounted() {
    this.catalogViewport = window.matchMedia('(max-width: 767px)');
    this.syncCatalogViewport = () => {
        if (this.isDrawerActive.filter || this.isDrawerActive.toolbar) document.body.style.overflow = '';
        this.isMobile = this.catalogViewport.matches;
        this.isDrawerActive = { toolbar: false, filter: false };
    };
    this.catalogViewport.addEventListener('change', this.syncCatalogViewport);
},

beforeUnmount() {
    this.catalogViewport?.removeEventListener('change', this.syncCatalogViewport);
},
