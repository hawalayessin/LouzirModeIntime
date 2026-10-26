<?php

use Illuminate\Support\Facades\DB;
use Webkul\Category\Models\Category;

beforeEach(function () {
    DB::table('attributes')->update(['is_filterable' => 0]);
    $this->priceId = DB::table('attributes')->where('code', 'price')->value('id');
    DB::table('attributes')->where('id', $this->priceId)->update(['is_filterable' => 1]);
    $this->category = Category::factory()->create(['parent_id' => core()->getCurrentChannel()->root_category_id]);
    $this->category->filterableAttributes()->detach();
});

it('falls back to global admin filters when a category has no assigned filters', function () {
    $this->getJson(route('shop.api.categories.attributes', ['category_id' => $this->category->id]))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', 'price');
});

it('keeps explicitly assigned category filters ahead of global filters', function () {
    $colorId = DB::table('attributes')->where('code', 'color')->value('id');
    $this->category->filterableAttributes()->sync([$colorId]);
    $this->getJson(route('shop.api.categories.attributes', ['category_id' => $this->category->id]))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', 'color');
});

it('uses global admin filters on search and honors a disabled filter', function () {
    $this->getJson(route('shop.api.categories.attributes'))
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.code', 'price');
    DB::table('attributes')->where('id', $this->priceId)->update(['is_filterable' => 0]);
    $this->getJson(route('shop.api.categories.attributes'))->assertOk()->assertJsonCount(0, 'data');
});

it('returns not found for an unknown filter category', function () {
    $this->getJson(route('shop.api.categories.attributes', ['category_id' => 2147483647]))->assertNotFound();
});
