<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Models\CoreConfig;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Shop\Helpers\StorefrontText;
use Webkul\Theme\Facades\Themes as ThemeFacade;
use Webkul\Theme\Themes;

beforeEach(function () {
    $this->loginAsAdmin();
    $this->channel = core()->getDefaultChannel()->code;
    CoreConfig::where('code', 'like', 'general.louzir_home.storefront.%')->delete();
    $this->saveDesign = fn (array $settings) => $this->post(route('admin.configuration.store', ['slug' => 'general', 'slug2' => 'louzir_home']), [
        'channel' => $this->channel, 'locale' => 'fr', 'general' => ['louzir_home' => ['storefront' => $settings]],
    ]);
});

it('publishes admin design tokens and fonts on all storefront layouts', function () {
    ($this->saveDesign)(['design-background' => '#f6f1e9', 'design-accent' => '#ad8959', 'design-surface' => '#fff8ee', 'design-panel' => '#ebdbc3', 'design-button' => '#443322', 'design-button-text' => '#ffffff', 'design-muted' => '#665544', 'design-accent-strong' => '#886644', 'design-body-font' => 'inter', 'design-heading-font' => 'georgia', 'design-width' => '1320'])
        ->assertSessionHasNoErrors()->assertSessionHas('success');
    foreach (['/?locale=fr', '/search?query=test', '/checkout/cart', '/customer/login', '/customer/register', '/customer/forgot-password', '/compare'] as $path) {
        ThemeFacade::clearResolvedInstance(Themes::class);
        $this->get($path)->assertOk()->assertSee('class="louzir-storefront"', false)
            ->assertSee('--store-background: #f6f1e9', false)
            ->assertSee('--store-accent: #ad8959', false)
            ->assertSee('--store-surface: #fff8ee', false)
            ->assertSee('--store-panel: #ebdbc3', false)
            ->assertSee('--store-button: #443322', false)
            ->assertSee('--store-button-text: #ffffff', false)
            ->assertSee('--store-muted: #665544', false)
            ->assertSee('--store-accent-strong: #886644', false)
            ->assertSee("--store-body-font: 'Inter'", false)
            ->assertSee("--store-heading-font: 'Georgia'", false)
            ->assertSee('--store-width: 1320px', false);
    }
});

it('lets administrators unify headings with the selected body font', function () {
    ($this->saveDesign)(['design-body-font' => 'manrope', 'design-heading-font' => 'body'])
        ->assertSessionHasNoErrors()->assertSessionHas('success');

    $this->get('/?locale=fr')->assertOk()
        ->assertSee("--store-body-font: 'Manrope'", false)
        ->assertSee("--store-heading-font: 'Manrope'", false)
        ->assertSee('assets/styles/ui-fonts.css', false)
        ->assertDontSee('fonts.googleapis.com/css2?', false);

    ($this->saveDesign)(['design-body-font' => 'inter', 'design-heading-font' => 'body'])
        ->assertSessionHasNoErrors();

    ThemeFacade::clearResolvedInstance(Themes::class);
    $this->get('/customer/login')->assertOk()
        ->assertSee("--store-body-font: 'Inter'", false)
        ->assertSee("--store-heading-font: 'Inter'", false);
});

it('renders one rich description and native product panels using merchant content', function () {
    $product = (new ProductFaker)->getSimpleProductFactory()->create();
    $description = '<h3>Merchant composition</h3><ul><li>Merchant cotton</li></ul><p>{{ literal text }}</p>';
    DB::table('product_attribute_values')->where('product_id', $product->id)
        ->where('attribute_id', DB::table('attributes')->where('code', 'description')->value('id'))
        ->update(['text_value' => $description]);
    DB::table('product_flat')->where('product_id', $product->id)->update(['description' => $description]);
    ThemeFacade::clearResolvedInstance(Themes::class);
    $html = $this->get('/'.$product->url_key.'?locale=en')->assertOk()
        ->assertSee('<h3>Merchant composition</h3>', false)
        ->assertSee('id="product-reviews"', false)
        ->assertSee('data-product-description v-pre', false)->getContent();
    expect(substr_count($html, 'data-product-description'))->toBe(1)
        ->and($html)->not->toContain('descritpion-tab');
});

it('uses category banners ahead of the configurable collection image and preserves display modes', function () {
    $category = app(CategoryRepository::class)->create([
        'position' => 1, 'status' => 1, 'parent_id' => core()->getDefaultChannel()->root_category_id,
        'display_mode' => 'products_only', 'locale' => 'en',
        'en' => ['name' => 'Merchant category', 'slug' => 'merchant-theme-category', 'description' => '<p>Hidden merchant description</p>', 'locale_id' => 1],
    ]);
    $category->forceFill(['banner_path' => 'category/merchant-banner.webp'])->save();
    ThemeFacade::clearResolvedInstance(Themes::class);
    $this->get('/merchant-theme-category?locale=en')->assertOk()
        ->assertSee('Merchant category')->assertSee('category/merchant-banner.webp', false)
        ->assertDontSee('<p>Hidden merchant description</p>', false);
});

it('uploads a default collection image through the admin', function () {
    Storage::fake(config('filesystems.default'));
    ($this->saveDesign)(['design-category-image' => UploadedFile::fake()->image('collection.png', 1440, 480)])
        ->assertSessionHasNoErrors()->assertSessionHas('success');
    $record = CoreConfig::where('code', 'general.louzir_home.storefront.design-category-image')->firstOrFail();
    Storage::assertExists($record->value);
});

it('validates store wide appearance without trusting submitted field metadata', function ($field, $value) {
    ($this->saveDesign)([$field => $value])->assertSessionHasErrors('general.louzir_home.storefront.'.$field);
})->with([
    ['design-background', 'red'], ['design-text', '#fff'], ['design-width', '1199'], ['design-width', '1601'],
    ['design-body-font', 'url(malicious)'], ['design-heading-font', 'missing-font'], ['design-category-hero', 'yes'],
    ['design-surface', 'white'], ['design-panel', '#fff'], ['design-button', 'url(evil)'],
    ['design-button-text', '#gggggg'], ['design-muted', 'transparent'], ['design-accent-strong', '#1234'],
]);

it('localizes default storefront copy while preserving merchant content', function ($locale) {
    app()->setLocale($locale);
    $dictionary = require __DIR__.'/../../../../Shop/src/Resources/lang/'.$locale.'/storefront.php';
    $english = require __DIR__.'/../../../../Shop/src/Resources/lang/en/storefront.php';
    expect(array_keys($dictionary))->toBe(array_keys($english));
    foreach ($dictionary as $key => $value) {
        expect(trans('shop::storefront.'.$key))->toBe($value);
    }
    expect(StorefrontText::legacyDefault('About Us'))->toBe($dictionary['about-us'])
        ->and(StorefrontText::legacyDefault('Cookie Block'))->toBe($dictionary['cookie-title'])
        ->and(StorefrontText::legacyDefault('Notre maison depuis 1995'))->toBe('Notre maison depuis 1995')
        ->and(StorefrontText::legacyDefault(null))->toBe('');
})->with(['fr', 'en', 'ar']);
