<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Webkul\Shop\Helpers\TunisiaMunicipalities;

it('groups Tunisian localities by governorate and keeps the postal codes from the API payload', function () {
    Cache::flush();

    Http::fake([
        'https://tn-municipality-api.vercel.app/api/municipalities' => Http::response([
            ['Value' => 'ARIANA', 'Name' => 'ARIANA', 'NameAr' => 'أريانة', 'PostalCode' => '2030'],
            ['Value' => 'ARIANA', 'Name' => 'ARIANA (Cité El Menzah)', 'NameAr' => 'أريانة (حي المنزه)', 'PostalCode' => '2037'],
            ['Value' => 'BEJA', 'Name' => 'BEJA', 'NameAr' => 'باجة', 'PostalCode' => '9000'],
        ]),
    ]);

    $regions = app(TunisiaMunicipalities::class)->all();

    expect($regions)->toHaveCount(2)
        ->and($regions[0]['value'])->toBe('ARIANA')
        ->and($regions[0]['localities'])->toHaveCount(2)
        ->and($regions[0]['localities'][0]['postcode'])->toBe('2030')
        ->and($regions[1]['localities'][0]['postcode'])->toBe('9000');
});

it('deduplicates localities and ignores invalid postal codes in grouped API responses', function () {
    Cache::flush();
    Http::fake(['*' => Http::response([
        ['Value' => 'TUNIS', 'NameAr' => 'تونس', 'Delegations' => [
            ['Name' => 'TUNIS CENTRE', 'PostalCode' => '1000'],
            ['Name' => 'TUNIS CENTRE', 'PostalCode' => '1000'],
            ['Name' => 'INVALID', 'PostalCode' => '12345'],
            ['Name' => '', 'PostalCode' => '1000'],
        ]],
    ])]);
    $regions = app(TunisiaMunicipalities::class)->all();
    expect($regions)->toHaveCount(1)->and($regions[0]['localities'])->toHaveCount(1)
        ->and($regions[0]['localities'][0]['postcode'])->toBe('1000');
});

it('caches API data and uses the last successful response during an outage', function () {
    Cache::flush();
    Http::fake(['*' => Http::response([
        ['Value' => 'TUNIS', 'Name' => 'TUNIS CENTRE', 'PostalCode' => '1000'],
    ])]);
    $helper = app(TunisiaMunicipalities::class);
    $regions = $helper->all();
    expect($helper->all())->toBe($regions);
    Http::assertSentCount(1);

    Cache::forget('shop.tunisia-municipalities.v1');
    Http::fake(['*' => Http::response([], 503)]);
    expect($helper->all())->toBe($regions);
});

it('returns no selectable locations when the API is unavailable without cached data', function () {
    Cache::flush();
    Http::fake(['*' => Http::response([], 503)]);
    expect(app(TunisiaMunicipalities::class)->all())->toBe([]);
});
