<?php

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\Shop\Helpers\FilterLabels;

beforeEach(function () {
    $ids = Attribute::whereIn('code', ['color', 'size', 'brand'])->pluck('id');
    DB::table('attribute_translations')->whereIn('attribute_id', $ids)->delete();
    foreach (['color' => 'Color', 'size' => 'Size', 'brand' => 'Brand'] as $code => $name) {
        $attribute = Attribute::where('code', $code)->firstOrFail();
        $attribute->admin_name = $name;
        $attribute->translateOrNew('en')->name = $name;
        $attribute->save();
    }
    $this->color = Attribute::where('code', 'color')->firstOrFail();
    $this->color->options()->delete();
    $this->red = $this->color->options()->create(['admin_name' => 'Red', 'sort_order' => 1]);
    $this->red->translateOrNew('en')->label = 'Red';
    $this->red->save();
});

it('localizes standard filter defaults without changing merchant records', function ($locale, $color, $size, $brand, $red) {
    app()->setLocale($locale);
    expect(FilterLabels::attribute($this->color))->toBe($color)
        ->and(FilterLabels::attribute(Attribute::where('code', 'size')->first()))->toBe($size)
        ->and(FilterLabels::attribute(Attribute::where('code', 'brand')->first()))->toBe($brand)
        ->and(FilterLabels::option($this->red))->toBe($red)
        ->and(FilterLabels::swatch($this->red))->toBe('#b85a5a');
    expect(DB::table('attribute_translations')->where('attribute_id', $this->color->id)->where('locale', $locale)->exists())->toBe($locale === 'en');
})->with([
    ['fr', 'Couleur', 'Taille', 'Marque', 'Rouge'],
    ['en', 'Color', 'Size', 'Brand', 'Red'],
    ['ar', 'اللون', 'المقاس', 'العلامة التجارية', 'أحمر'],
]);

it('gives admin translations and color swatches priority over defaults', function () {
    app()->setLocale('fr');
    $this->color->translateOrNew('fr')->name = 'Nuances de notre atelier';
    $this->color->save();
    $this->red->translateOrNew('fr')->label = 'Rouge cerise';
    $this->red->swatch_value = '#9b2233';
    $this->red->save();
    expect(FilterLabels::attribute($this->color))->toBe('Nuances de notre atelier')
        ->and(FilterLabels::option($this->red))->toBe('Rouge cerise')
        ->and(FilterLabels::swatch($this->red))->toBe('#9b2233');
});

it('searches translated default color names and preserves real option ids', function () {
    $this->getJson(route('shop.api.categories.attribute_options', ['attribute_id' => $this->color->id, 'search' => 'Rouge', 'locale' => 'fr']))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $this->red->id)
        ->assertJsonPath('data.0.name', 'Rouge')->assertJsonPath('data.0.swatch', '#b85a5a');
});

it('does not substitute default search names for a translated merchant option', function () {
    $this->red->translateOrNew('fr')->label = 'Cerise exclusive';
    $this->red->save();
    $this->getJson(route('shop.api.categories.attribute_options', ['attribute_id' => $this->color->id, 'search' => 'Rouge', 'locale' => 'fr']))
        ->assertOk()->assertJsonCount(0, 'data');
    $this->getJson(route('shop.api.categories.attribute_options', ['attribute_id' => $this->color->id, 'search' => 'Cerise', 'locale' => 'fr']))
        ->assertOk()->assertJsonPath('data.0.name', 'Cerise exclusive');
});

it('never interprets an admin swatch as CSS code', function () {
    $this->red->swatch_value = 'url(https://example.invalid/tracker)';
    expect(FilterLabels::swatch($this->red))->toBe('#b85a5a');
});
