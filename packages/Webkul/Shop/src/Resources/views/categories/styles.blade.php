<style>
    .louzir-catalog-shell {
        width: 100%;
        max-width: 1600px;
        margin-inline: auto;
        padding-inline: 32px;
    }
    .louzir-catalog-layout { display: flex; align-items: flex-start; gap: 28px; }
    .louzir-category-banner {
        display: block;
        width: 100%;
        height: auto;
        aspect-ratio: 3 / 1;
        object-fit: cover;
        object-position: 50% 0%;
        border-radius: 16px;
    }
    .louzir-catalog-products { flex: 1; min-width: 0; }
    .louzir-filter-sidebar { flex: 0 0 260px; width: 260px; min-width: 0; }
    .louzir-filter-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        width: 100%;
        min-width: 0;
        max-height: 1320px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .louzir-range-label { display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 8px; }
    .louzir-range-label p { font-size: 14px; overflow-wrap: anywhere; }
    .louzir-range-track { height: 44px; padding-inline: 10px; }
    .louzir-range-track > div,
    .louzir-range-track input[type="range"] { height: 3px; }
    .louzir-range-track input[type="range"]::-webkit-slider-thumb {
        width: 18px;
        height: 18px;
        border: 2px solid #090c3b;
        box-shadow: none;
    }
    .louzir-range-track input[type="range"]::-moz-range-thumb {
        width: 18px;
        height: 18px;
        box-sizing: border-box;
        border: 2px solid #090c3b;
        box-shadow: none;
    }
    .louzir-range-track input[type="range"]:focus-visible::-webkit-slider-thumb {
        box-shadow: 0 0 0 4px rgba(9, 12, 59, .15);
    }
    .louzir-range-track input[type="range"]:focus-visible::-moz-range-thumb {
        box-shadow: 0 0 0 4px rgba(9, 12, 59, .15);
    }
    @media (min-width: 768px) and (max-width: 1023px) {
        .louzir-filter-sidebar { flex-basis: 220px; width: 220px; }
        .louzir-catalog-layout { gap: 20px; }
        .louzir-catalog-shell { padding-inline: 24px; }
    }
    @media (max-width: 767px) {
        .louzir-category-banner { aspect-ratio: 16 / 9; border-radius: 12px; }
        .louzir-catalog-shell { padding-inline: 16px; }
        .louzir-filter-panel { max-height: none; overflow: visible; }
        .louzir-catalog-layout { gap: 0; }
    }
</style>
