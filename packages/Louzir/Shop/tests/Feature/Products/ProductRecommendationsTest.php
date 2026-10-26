<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Category\Models\Category;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Faker\Helpers\Product as ProductFaker;

beforeEach(function () {
    config(['cache.default' => 'array']);
    Cache::flush();
    app()->setLocale('en');
    $this->channel = core()->getCurrentChannel();
    CoreConfig::where('code', 'like', 'catalog.products.product_view_page.%')->delete();
    $this->setRecommendation = function (string $key, mixed $value) {
        app(CoreConfigRepository::class)->create([
            'channel' => $this->channel->code, 'locale' => 'en',
            'catalog' => ['products' => ['product_view_page' => [$key => (string) $value]]],
        ]);
    };
    $this->makeProduct = function (float $price = 100, array $attributes = []) {
        $product = (new ProductFaker(['attribute_value' => array_merge([
            'price' => ['float_value' => $price],
            'manage_stock' => ['boolean_value' => 0, 'channel' => $this->channel->code],
        ], $attributes)]))->getSimpleProductFactory()->create();
        $product->categories()->detach();

        return $product;
    };
    $this->parentCategory = Category::factory()->create(['parent_id' => $this->channel->root_category_id]);
    $this->leafCategory = Category::factory()->create(['parent_id' => $this->parentCategory->id]);
    $this->source = ($this->makeProduct)(100);
    $this->source->categories()->sync([$this->parentCategory->id, $this->leafCategory->id]);
    $this->api = '/api/products/'.$this->source->id.'/related?locale=en';
});

it('prioritizes the most specific shared category then price proximity with stable ties', function () {
    $broad = ($this->makeProduct)(101);
    $broad->categories()->sync([$this->parentCategory->id]);
    $far = ($this->makeProduct)(180);
    $near = ($this->makeProduct)(102);
    $tie = ($this->makeProduct)(102);
    foreach ([$far, $near, $tie] as $product) {
        $product->categories()->sync([$this->leafCategory->id]);
    }
    $this->getJson($this->api)->assertOk()->assertJsonPath('data.0.id', $near->id)->assertJsonPath('data.1.id', $tie->id)->assertJsonPath('data.2.id', $far->id)->assertJsonPath('data.3.id', $broad->id);
});

it('preserves manual priority and supports manual only category only and explicit zero', function () {
    $manual = ($this->makeProduct)(300);
    $auto = ($this->makeProduct)(101);
    $auto->categories()->sync([$this->leafCategory->id]);
    $this->source->related_products()->sync([$manual->id]);
    $this->getJson($this->api)->assertJsonPath('data.0.id', $manual->id)->assertJsonCount(2, 'data');
    ($this->setRecommendation)('related_mode', 'manual');
    $this->getJson($this->api)->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $manual->id);
    ($this->setRecommendation)('related_mode', 'category');
    $this->getJson($this->api)->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $auto->id);
    ($this->setRecommendation)('no_of_related_products', 0);
    $this->getJson($this->api)->assertJsonCount(0, 'data');
});

it('excludes the source unpublished hidden variants and products outside the current channel', function () {
    $disabled = ($this->makeProduct)(100, ['status' => ['boolean_value' => 0, 'channel' => $this->channel->code]]);
    $hidden = ($this->makeProduct)(100, ['visible_individually' => ['boolean_value' => 0]]);
    $otherChannel = ($this->makeProduct)();
    $otherChannel->channels()->detach();
    $variant = ($this->makeProduct)();
    DB::table('product_flat')->where('product_id', $variant->id)->update(['visible_individually' => 0]);
    $this->source->related_products()->sync([$this->source->id, $disabled->id, $hidden->id, $otherChannel->id, $variant->id]);
    ($this->setRecommendation)('related_mode', 'manual');
    $this->getJson($this->api)->assertOk()->assertJsonCount(0, 'data');
    $this->getJson('/api/products/'.$disabled->id.'/related?locale=en')->assertNotFound();
    $this->getJson('/api/products/999999999/related?locale=en')->assertNotFound();
});

it('does not suggest unrelated catalog items or match only the channel root', function () {
    $unrelated = ($this->makeProduct)();
    $this->source->categories()->sync([$this->channel->root_category_id]);
    $unrelated->categories()->sync([$this->channel->root_category_id]);
    $this->getJson($this->api)->assertOk()->assertJsonCount(0, 'data');
});

it('rechecks publication stock and prices even when the ranking is cached', function () {
    $candidate = ($this->makeProduct)(101);
    $candidate->categories()->sync([$this->leafCategory->id]);
    $this->getJson($this->api)->assertJsonPath('data.0.id', $candidate->id);
    DB::table('product_attribute_values')->where('product_id', $candidate->id)->where('attribute_id', DB::table('attributes')->where('code', 'manage_stock')->value('id'))->update(['boolean_value' => 1]);
    DB::table('product_inventory_indices')->where('product_id', $candidate->id)->update(['qty' => 0]);
    $this->getJson($this->api)->assertJsonCount(0, 'data');
    ($this->setRecommendation)('related_only_saleable', 0);
    $this->getJson($this->api)->assertJsonCount(1, 'data')->assertJsonPath('data.0.is_saleable', false);
    DB::table('product_attribute_values')->where('product_id', $candidate->id)->where('attribute_id', DB::table('attributes')->where('code', 'status')->value('id'))->update(['boolean_value' => 0]);
    $this->getJson($this->api)->assertJsonCount(0, 'data');
});

it('invalidates ranked identifiers after an admin product update event', function () {
    $one = ($this->makeProduct)(101);
    $two = ($this->makeProduct)(102);
    $this->source->related_products()->sync([$one->id]);
    ($this->setRecommendation)('related_mode', 'manual');
    $this->getJson($this->api)->assertJsonPath('data.0.id', $one->id);
    $this->source->related_products()->sync([$two->id]);
    Event::dispatch('catalog.product.update.after', $this->source->fresh());
    $this->getJson($this->api)->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $two->id);
});

it('does not cache customer facing price values and filters upsells too', function () {
    $candidate = ($this->makeProduct)(101);
    $this->source->related_products()->sync([$candidate->id]);
    $this->source->up_sells()->sync([$candidate->id, $this->source->id]);
    $first = $this->getJson($this->api)->assertOk()->json('data.0.min_price');
    DB::table('product_price_indices')->where('product_id', $candidate->id)->update(['min_price' => 66, 'regular_min_price' => 66, 'max_price' => 66, 'regular_max_price' => 66]);
    expect($this->getJson($this->api)->json('data.0.min_price'))->not->toBe($first);
    $this->getJson('/api/products/'.$this->source->id.'/up-sell?locale=en')->assertOk()->assertJsonCount(1, 'data');
});

it('reuses only rankings and aggregates ratings without loading review bodies', function () {
    $candidate = ($this->makeProduct)();
    $candidate->categories()->sync([$this->leafCategory->id]);
    $this->getJson($this->api)->assertOk();
    DB::enableQueryLog();
    DB::flushQueryLog();
    $this->getJson($this->api)->assertOk()->assertJsonCount(1, 'data');
    $queries = collect(DB::getQueryLog())->pluck('query');
    DB::disableQueryLog();
    expect($queries->filter(fn ($sql) => str_contains($sql, 'category_span')))->toBeEmpty()
        ->and($queries->filter(fn ($sql) => str_starts_with($sql, 'select * from `product_reviews`')))->toBeEmpty();
});

it('keeps recommendation query growth bounded from one to twelve displayed products', function () {
    for ($i = 0; $i < 12; $i++) {
        $product = ($this->makeProduct)(100 + $i);
        $product->categories()->sync([$this->leafCategory->id]);
    }
    $measure = function (int $limit) {
        ($this->setRecommendation)('no_of_related_products', $limit);
        $this->getJson($this->api)->assertOk();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $response = $this->getJson($this->api)->assertOk()->assertJsonCount($limit, 'data');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        return count($queries);
    };
    $one = $measure(1);
    $twelve = $measure(12);
    fwrite(STDOUT, "\nRecommendation SQL queries: one={$one}; twelve={$twelve}\n");
    expect($twelve)->toBeLessThanOrEqual($one + 2)->and($twelve)->toBeLessThan(45);
});

it('separates ranking caches for customer groups and serves their live prices', function () {
    $one = ($this->makeProduct)(101);
    $two = ($this->makeProduct)(150);
    foreach ([$one, $two] as $product) {
        $product->categories()->sync([$this->leafCategory->id]);
    }
    $this->getJson($this->api)->assertJsonPath('data.0.id', $one->id);
    $group = CustomerGroup::factory()->create();
    foreach ([[$this->source, 200], [$one, 120], [$two, 201]] as [$product, $price]) {
        DB::table('product_price_indices')->insert(['product_id' => $product->id, 'channel_id' => $this->channel->id, 'customer_group_id' => $group->id,
            'min_price' => $price, 'max_price' => $price, 'regular_min_price' => $price, 'regular_max_price' => $price]);
    }
    $customer = Customer::factory()->create(['customer_group_id' => $group->id]);
    $this->loginAsCustomer($customer);
    $this->getJson($this->api)->assertOk()->assertJsonPath('data.0.id', $two->id);
});

it('does not serve another language from the ranking cache', function () {
    $candidate = ($this->makeProduct)(101);
    $candidate->categories()->sync([$this->leafCategory->id]);
    $this->getJson($this->api)->assertJsonCount(1, 'data');
    $sourceFlat = (array) DB::table('product_flat')->where('product_id', $this->source->id)->where('locale', 'en')->first();
    unset($sourceFlat['id']);
    $sourceFlat['locale'] = 'fr';
    foreach (DB::table('product_attribute_values')->where('product_id', $this->source->id)->where('locale', 'en')
        ->whereIn('attribute_id', DB::table('attributes')->whereIn('code', ['name', 'url_key'])->pluck('id'))->get() as $value) {
        $translated = (array) $value;
        unset($translated['id']);
        $translated['locale'] = 'fr';
        $translated['unique_id'] = 'fr|'.$this->source->id.'|'.$value->attribute_id;
        DB::table('product_attribute_values')->insert($translated);
    }
    DB::table('product_flat')->updateOrInsert(['product_id' => $this->source->id, 'channel' => $this->channel->code, 'locale' => 'fr'], $sourceFlat);
    DB::table('product_flat')->where('product_id', $candidate->id)->where('locale', 'fr')->delete();
    $this->getJson(str_replace('locale=en', 'locale=fr', $this->api))->assertOk()->assertJsonCount(0, 'data');
});

it('preserves the existing global zero setting for related products', function () {
    CoreConfig::create(['code' => 'catalog.products.product_view_page.no_of_related_products', 'value' => '0']);
    $this->getJson($this->api)->assertOk()->assertJsonCount(0, 'data');
});
