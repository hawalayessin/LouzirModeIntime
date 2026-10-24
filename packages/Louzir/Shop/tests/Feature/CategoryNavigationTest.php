<?php

use Database\Seeders\LingerieCategoriesSeeder;
use Illuminate\Support\Facades\DB;
use Webkul\Category\Repositories\CategoryRepository;

it('adds the reference category tree idempotently while preserving merchant catalog data', function () {
    $repository = app(CategoryRepository::class);
    $root = core()->getDefaultChannel()->root_category_id;
    $productId = DB::table('products')->insertGetId([
        'sku' => 'MERCHANT-PRESERVED', 'type' => 'simple',
        'attribute_family_id' => DB::table('attribute_families')->value('id'),
    ]);
    DB::table('product_categories')->insert(['product_id' => $productId, 'category_id' => $root]);
    $productCount = DB::table('products')->count();
    $this->seed(LingerieCategoriesSeeder::class);
    $firstCount = DB::table('categories')->count();
    $bras = $repository->findBySlug('soutiens-gorges');
    $child = $repository->findBySlug('soutiens-gorges-classiques');
    expect((int) $bras->parent_id)->toBe((int) $root)
        ->and((int) $child->parent_id)->toBe($bras->id);
    $bras->position = 42;
    $bras->save();
    $this->seed(LingerieCategoriesSeeder::class);
    expect(DB::table('categories')->count())->toBe($firstCount)
        ->and(DB::table('products')->count())->toBe($productCount)
        ->and($bras->fresh()->position)->toBe(42);
    $this->assertDatabaseHas('product_categories', ['product_id' => $productId, 'category_id' => $root]);
    $this->get('/?locale=fr')->assertOk()->assertSee('v-category-branch', false);
});
