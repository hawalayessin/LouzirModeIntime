<?php

use Database\Seeders\ProductAttributeLabelsSeeder;
use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\Faker\Helpers\Product as ProductFaker;

it('keeps editable attribute labels when translations are missing or blank', function () {
    $product = (new ProductFaker)->getSimpleProductFactory()->create();
    $group = $product->attribute_family->attribute_groups->firstWhere('code', 'settings');
    $attribute = Attribute::where('code', 'status')->firstOrFail();
    DB::table('attribute_translations')->where('attribute_id', $attribute->id)->where('locale', 'fr')->delete();
    app()->setLocale('fr');
    expect($product->getEditableAttributes($group)->firstWhere('code', 'status')->admin_name)->toBe($attribute->admin_name);
    DB::table('attribute_translations')->insert(['attribute_id' => $attribute->id, 'locale' => 'fr', 'name' => '   ']);
    expect($product->getEditableAttributes($group)->firstWhere('code', 'status')->admin_name)->toBe($attribute->admin_name);
    DB::table('attribute_translations')->where('attribute_id', $attribute->id)->where('locale', 'fr')->update(['name' => 'Disponible en boutique']);
    expect($product->getEditableAttributes($group)->firstWhere('code', 'status')->admin_name)->toBe('Disponible en boutique');
});

it('opens product editing with visible localized settings and preserves custom attribute labels', function () {
    $this->loginAsAdmin();
    $product = (new ProductFaker)->getSimpleProductFactory()->create();
    $attribute = Attribute::where('code', 'status')->firstOrFail();
    DB::table('attribute_translations')->updateOrInsert(['attribute_id' => $attribute->id, 'locale' => 'fr'], ['name' => 'Disponible en boutique']);
    $this->seed(ProductAttributeLabelsSeeder::class);
    $this->get(route('admin.catalog.products.edit', ['id' => $product->id, 'locale' => 'fr']))->assertOk()
        ->assertSee('Disponible en boutique')
        ->assertSee('for="status"', false);
    expect(DB::table('attribute_translations')->where('attribute_id', $attribute->id)->where('locale', 'fr')->value('name'))->toBe('Disponible en boutique');
});

it('saves product settings and prices through the admin endpoint', function () {
    $this->loginAsAdmin();
    $product = (new ProductFaker)->getSimpleProductFactory()->create();
    $url = route('admin.catalog.products.update', $product->id);
    $payload = [
        'sku' => $product->sku, 'url_key' => $product->url_key,
        'name' => 'Produit administrable', 'short_description' => 'Description courte', 'description' => 'Description du produit',
        'price' => '99.5', 'weight' => '1', 'channel' => core()->getDefaultChannel()->code, 'locale' => app()->getLocale(),
        'channels' => [$product->channels()->first()?->id ?? core()->getDefaultChannel()->id],
        'categories' => $product->categories()->pluck('categories.id')->all(),
        'status' => '0', 'visible_individually' => '1', 'guest_checkout' => '1', 'new' => '0', 'featured' => '0',
    ];
    $this->put($url, $payload)->assertSessionHasNoErrors()->assertRedirect();
    expect((int) $product->fresh()->status)->toBe(0)
        ->and((float) $product->fresh()->price)->toBe(99.5);
    $payload['status'] = '1';
    $this->put($url, $payload)->assertSessionHasNoErrors()->assertRedirect();
    expect((int) $product->fresh()->status)->toBe(1);
});
