<?php

use Database\Seeders\StoreDeliveryOptionsSeeder;
use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartAddress;
use Webkul\Core\Models\CoreConfig;
use Webkul\Shipping\Carriers\Express;
use Webkul\Shipping\Carriers\FlatRate;

beforeEach(function () {
    $this->loginAsAdmin();
    CoreConfig::where('code', 'like', 'sales.carriers.%')->delete();
    $this->seed(StoreDeliveryOptionsSeeder::class);
    $this->url = route('admin.configuration.store', ['slug' => 'sales', 'slug2' => 'carriers']);
    $this->payload = ['channel' => core()->getDefaultChannel()->code, 'locale' => 'fr'];
});

it('exposes both delivery options and their prices in admin', function () {
    $this->get($this->url)->assertOk()
        ->assertSee('sales[carriers][flatrate][default_rate]', false)
        ->assertSee('sales[carriers][express][default_rate]', false)
        ->assertSee('sales[carriers][express][active]', false);
    expect((new FlatRate)->getRate()->base_price)->toEqual(8)
        ->and((new Express)->getRate()->base_price)->toEqual(15);
});

it('lets the customer select standard at eight dinars or express at fifteen dinars', function () {
    $cart = Cart::factory()->create();
    CartAddress::factory()->create(['cart_id' => $cart->id, 'address_type' => CartAddress::ADDRESS_TYPE_SHIPPING]);
    cart()->setCart($cart->fresh());
    $methods = shipping()->collectRates()['shippingMethods'];
    expect(array_keys($methods))->toBe(['flatrate', 'express']);
    foreach (['flatrate_flatrate', 'express_express'] as $code) {
        expect(cart()->saveShippingMethod($code))->toBeTrue()
            ->and($cart->fresh()->shipping_method)->toBe($code);
        $rate = $cart->fresh()->shipping_rates()->where('method', $code)->firstOrFail();
        expect((float) $rate->base_price)->toBe($code === 'express_express' ? 15.0 : 8.0);
    }
});

it('publishes admin price and title edits and respects activation', function () {
    $this->post($this->url, $this->payload + ['sales' => ['carriers' => ['express' => [
        'active' => '1', 'default_rate' => '9.500', 'type' => 'per_order', 'title' => 'Express personnalisé',
    ]]]])->assertSessionHasNoErrors()->assertSessionHas('success');
    expect((float) (new Express)->getRate()->base_price)->toBe(9.5);
    expect(core()->getConfigData('sales.carriers.express.title', $this->payload['channel'], 'fr'))->toBe('Express personnalisé');
    $this->post($this->url, $this->payload + ['sales' => ['carriers' => ['flatrate' => ['active' => '1'], 'express' => ['active' => '0']]]])
        ->assertSessionHasNoErrors()->assertSessionHas('success');
    expect((new Express)->calculate())->toBeFalse();
});

it('validates delivery settings without relying on client metadata', function ($field, $value) {
    $this->post($this->url, $this->payload + ['sales' => ['carriers' => ['express' => [$field => $value]]]])
        ->assertSessionHasErrors('sales.carriers.express.'.$field);
})->with([['default_rate', '-1'], ['default_rate', '8,000'], ['default_rate', 'not-a-price'], ['active', 'wrong'], ['type', 'wrong']]);

it('preserves merchant shipping settings when defaults are initialized again', function () {
    CoreConfig::where('code', 'sales.carriers.express.default_rate')->update(['value' => '12.5']);
    $this->seed(StoreDeliveryOptionsSeeder::class);
    expect((float) (new Express)->getRate()->base_price)->toBe(12.5);
});
