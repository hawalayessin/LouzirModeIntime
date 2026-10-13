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
