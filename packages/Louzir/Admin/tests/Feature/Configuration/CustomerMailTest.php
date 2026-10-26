<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Webkul\Core\Mail\Transport\DynamicSmtpTransport;
use Webkul\Core\Models\CoreConfig;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Shop\Mail\Order\ConfirmedNotification;

beforeEach(function () {
    $this->loginAsAdmin();
    Mail::fake();
    $this->saveEmail = fn ($settings) => $this->post(route('admin.configuration.store', ['slug' => 'emails', 'slug2' => 'configure']), ['channel' => core()->getCurrentChannel()->code, 'locale' => 'fr', 'emails' => ['configure' => $settings]]);
});

it('confirms and queues a customer email once even if the action is repeated', function () {
    $order = Order::factory()->create(['customer_email' => 'customer@example.test']);
    OrderItem::factory()->create(['order_id' => $order->id, 'additional' => ['locale' => 'fr']]);
    $this->post(route('admin.sales.orders.confirm', $order->id))->assertRedirect();
    $this->post(route('admin.sales.orders.confirm', $order->id))->assertRedirect()->assertSessionHas('error');
    expect($order->fresh()->status)->toBe(Order::STATUS_PROCESSING);
    Mail::assertQueued(ConfirmedNotification::class, 1);
});

it('encrypts the SMTP credential and preserves it when the password field is left blank', function () {
    ($this->saveEmail)(['smtp' => ['password' => 'test-app-password']])->assertSessionHasNoErrors();
    expect(DB::table('core_config')->where('code', 'emails.configure.smtp.password')->value('value'))->toStartWith('smtp:v1:');
    expect(core()->getConfigData('emails.configure.smtp.password'))->toBe('test-app-password');
    ($this->saveEmail)(['smtp' => ['password' => '']])->assertSessionHasNoErrors();
    expect(core()->getConfigData('emails.configure.smtp.password'))->toBe('test-app-password');
    $this->get(route('admin.configuration.index', ['slug' => 'emails', 'slug2' => 'configure']))->assertOk()->assertDontSee('test-app-password');
});

it('enforces SMTP validation without trusting client field metadata', function ($name, $value) {
    ($this->saveEmail)(['smtp' => [$name => $value]])->assertSessionHasErrors('emails.configure.smtp.'.$name);
})->with([['port', 0], ['port', 65536], ['port', 'invalid'], ['encryption', 'off'], ['host', '']]);

it('fails before connecting when SMTP credentials are incomplete', function () {
    foreach (['host' => 'smtp.gmail.com', 'username' => 'sender@example.test', 'password' => ''] as $name => $value) {
        CoreConfig::where('code', 'emails.configure.smtp.'.$name)->delete();
        CoreConfig::create(['code' => 'emails.configure.smtp.'.$name, 'value' => $value]);
    }
    expect(fn () => (new class extends DynamicSmtpTransport
    {
        public function transport()
        {
            return $this->buildTransport();
        }
    })->transport())->toThrow(RuntimeException::class, 'SMTP authentication requires a password');
});

it('builds an SMTP transport with the configured security mode and a bounded timeout', function ($mode, $auto, $required) {
    foreach (['host' => 'smtp.example.test', 'port' => '587', 'encryption' => $mode, 'username' => '', 'password' => ''] as $name => $value) {
        CoreConfig::where('code', 'emails.configure.smtp.'.$name)->delete();
        CoreConfig::create(['code' => 'emails.configure.smtp.'.$name, 'value' => $value]);
    }
    $transport = (new class extends DynamicSmtpTransport
    {
        public function transport()
        {
            return $this->buildTransport();
        }
    })->transport();
    expect($transport->isAutoTls())->toBe($auto)->and($transport->isTlsRequired())->toBe($required)
        ->and($transport->getStream()->getTimeout())->toBe(15.0);
})->with([['tls', true, true], ['ssl', true, false], ['none', false, false]]);
