<?php

use Illuminate\Support\Facades\DB;
use Webkul\Category\Models\Category;
use Webkul\FPC\Listeners\Category as CategoryCache;

it('invalidates category caches without requiring a translation in the channel default language', function () {
    $default = core()->getDefaultLocaleCodeFromDefaultChannel();
    $other = $default === 'fr' ? 'en' : 'fr';
    $category = Category::create([
        'position' => 1, 'status' => 1, 'parent_id' => core()->getDefaultChannel()->root_category_id,
        $other => ['name' => 'Cache fallback test', 'slug' => 'cache-fallback-test'],
    ]);
    expect($category->translate($default))->toBeNull();

    $listener = app(CategoryCache::class);
    $listener->afterUpdate($category);
    $listener->beforeDelete($category->id);

    $this->loginAsAdmin();
    $this->deleteJson(route('admin.catalog.categories.delete', $category->id))
        ->assertOk()->assertJsonPath('message', trans('admin::app.catalog.categories.delete-success'));
    expect(DB::table('categories')->where('id', $category->id)->exists())->toBeFalse();
});
