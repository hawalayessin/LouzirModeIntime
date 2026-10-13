<?php

it('provides a fresh non-cacheable newsletter session token', function () {
    $response = $this->getJson(route('shop.subscription.csrf-token'));

    $response->assertOk()->assertJsonStructure(['token']);

    expect($response->json('token'))->toBe(session()->token())
        ->and($response->headers->get('Cache-Control'))->toContain('no-store');
});
