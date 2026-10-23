<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\Shop\Http\Requests\CartAddressRequest;
use Webkul\Shop\Http\Requests\Customer\TunisiaAddressRequest;

beforeEach(function () {
    Cache::forget('shop.tunisia-municipalities.v1');
    Cache::forget('shop.tunisia-municipalities.v1.last-good');
    Http::preventStrayRequests();
    Http::fake([
        'https://tn-municipality-api.vercel.app/api/municipalities' => Http::response([
            ['Value' => 'TUNIS', 'Name' => 'TUNIS CENTRE', 'PostalCode' => '1000'],
            ['Value' => 'TUNIS', 'Name' => 'LE BARDO', 'PostalCode' => '2000'],
            ['Value' => 'SOUSSE', 'Name' => 'SOUSSE VILLE', 'PostalCode' => '4000'],
        ]),
    ]);
    $this->tnAddress = [
        'first_name' => 'Test', 'last_name' => 'Adresse',
        'email' => 'address-test@example.com', 'address' => ['10 rue de test'],
        'country' => 'TN', 'state' => 'TUNIS', 'city' => 'TUNIS CENTRE',
        'postcode' => '1000', 'phone' => '22123456',
    ];
});

it('validates Tunisian locations consistently in profile billing and shipping', function (string $context, array $changes, ?string $error) {
    $address = array_replace($this->tnAddress, $changes);
    if ($context === 'profile') {
        $payload = $address;
        $request = TunisiaAddressRequest::create('/', 'POST', $payload);
        $prefix = '';
    } else {
        $payload = ['billing' => [...$this->tnAddress, 'use_for_shipping' => $context === 'billing']];
        $payload[$context] = [...$address, ...($context === 'billing' ? ['use_for_shipping' => true] : [])];
        $request = CartAddressRequest::create('/', 'POST', $payload);
        $prefix = $context.'.';
    }

    $validator = Validator::make($payload, $request->rules());
    if ($error === null) {
        expect($validator->passes())->toBeTrue($validator->errors()->toJson());
    } else {
        expect($validator->errors()->has($prefix.$error))->toBeTrue($validator->errors()->toJson());
    }
})->with(['profile', 'billing', 'shipping'])->with([
    'valid' => [[], null],
    'foreign country' => [['country' => 'FR'], 'country'],
    'missing country' => [['country' => ''], 'country'],
    'unknown governorate' => [['state' => 'PARIS'], 'state'],
    'missing governorate' => [['state' => ''], 'state'],
    'foreign city' => [['city' => 'Paris'], 'city'],
    'wrong governorate' => [['city' => 'SOUSSE VILLE', 'postcode' => '4000'], 'city'],
    'missing city' => [['city' => ''], 'city'],
    'wrong postcode' => [['postcode' => '2000'], 'postcode'],
    'missing postcode' => [['postcode' => ''], 'postcode'],
    'malformed postcode' => [['postcode' => '10000'], 'postcode'],
]);

it('requires billing even when omitted from the checkout payload', function () {
    $request = CartAddressRequest::create('/', 'POST', ['shipping' => $this->tnAddress]);
    $validator = Validator::make($request->all(), $request->rules());
    expect($validator->errors()->has('billing.country'))->toBeTrue();
});

it('saves and updates Tunisian profile addresses through web and API endpoints', function (bool $api) {
    $customer = Customer::factory()->create();
    $this->actingAs($customer, 'customer');
    $createRoute = $api ? 'shop.api.customers.account.addresses.store' : 'shop.customers.account.addresses.store';
    $updateRoute = $api ? 'shop.api.customers.account.addresses.update' : 'shop.customers.account.addresses.update';
    $response = $this->postJson(route($createRoute), $this->tnAddress);
    $api ? $response->assertOk() : $response->assertRedirect();
    $address = CustomerAddress::where('customer_id', $customer->id)->firstOrFail();
    expect($address->country)->toBe('TN')->and($address->city)->toBe('TUNIS CENTRE')->and($address->postcode)->toBe('1000');

    $updated = [...$this->tnAddress, 'id' => $address->id, 'state' => 'SOUSSE', 'city' => 'SOUSSE VILLE', 'postcode' => '4000'];
    $response = $this->putJson(route($updateRoute, ['id' => $address->id]), $updated);
    $api ? $response->assertOk() : $response->assertRedirect();
    expect($address->fresh()->city)->toBe('SOUSSE VILLE')->and($address->fresh()->postcode)->toBe('4000');

    $this->putJson(route($updateRoute, ['id' => $address->id]), [...$updated, 'postcode' => '1000'])
        ->assertUnprocessable()->assertJsonValidationErrors('postcode');
    expect($address->fresh()->postcode)->toBe('4000');
})->with([true, false]);

it('rejects foreign addresses at the profile and checkout endpoints', function (string $route) {
    $customer = Customer::factory()->create();
    $this->actingAs($customer, 'customer');
    $address = [...$this->tnAddress, 'country' => 'FR', 'state' => 'PARIS', 'city' => 'Paris', 'postcode' => '75001'];
    $checkout = $route === 'shop.checkout.onepage.addresses.store';
    $payload = $checkout ? ['billing' => [...$address, 'use_for_shipping' => true]] : $address;
    $this->postJson(route($route), $payload)->assertUnprocessable()
        ->assertJsonValidationErrors($checkout ? ['billing.country', 'billing.state', 'billing.city', 'billing.postcode'] : ['country', 'state', 'city', 'postcode']);
    expect(CustomerAddress::where('customer_id', $customer->id)->count())->toBe(0);
})->with(['shop.customers.account.addresses.store', 'shop.api.customers.account.addresses.store', 'shop.checkout.onepage.addresses.store']);
