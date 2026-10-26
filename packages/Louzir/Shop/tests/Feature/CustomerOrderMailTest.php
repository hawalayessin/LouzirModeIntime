<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Models\SubscribersList;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Marketing\Models\Campaign;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Shop\Listeners\Customer;
use Webkul\Shop\Listeners\Order as OrderListener;
use Webkul\Shop\Mail\Customer\SubscriptionNotification;
use Webkul\Shop\Mail\Order\ConfirmedNotification;
use Webkul\Shop\Mail\Order\CreatedNotification;

beforeEach(function () {
    Mail::fake();
    $this->order = Order::factory()->create([
        'customer_email' => 'customer@example.test', 'customer_first_name' => 'Amira', 'customer_last_name' => 'Test',
        'is_guest' => 1, 'order_currency_code' => 'TND', 'sub_total' => 100, 'sub_total_incl_tax' => 100,
        'shipping_amount' => 8, 'shipping_amount_incl_tax' => 8, 'grand_total' => 108,
    ]);
    OrderItem::factory()->create(['order_id' => $this->order->id, 'name' => 'Parure de test', 'sku' => 'MAIL-TEST', 'type' => 'simple', 'price' => 100, 'total' => 100, 'additional' => ['locale' => 'fr']]);
});

it('queues the receipt in the order language without changing the application locale', function () {
    CoreConfig::updateOrCreate(['code' => 'emails.general.notifications.emails.general.notifications.new_order'], ['value' => '1']);
    app()->setLocale('en');
    app(OrderListener::class)->afterCreated($this->order);
    Mail::assertQueued(CreatedNotification::class, fn ($mail) => $mail->locale === 'fr' && $mail->afterCommit);
    expect(app()->getLocale())->toBe('en');
});

it('renders distinct personalized receipts and confirmations in each language', function ($locale) {
    app()->setLocale($locale);
    foreach ([new CreatedNotification($this->order), new ConfirmedNotification($this->order)] as $mail) {
        $html = $mail->locale($locale)->render();
        expect($html)->toContain('Amira', 'Parure de test', core()->formatPrice(108, 'TND'), 'MAIL-TEST')
            ->not->toContain('shop::order-mail.', route('shop.customers.account.orders.view', $this->order->id));
        if ($locale === 'ar') {
            expect($html)->toContain('dir="rtl"');
        }
    }
})->with(['fr', 'en', 'ar']);

it('uses admin message text as escaped text with supported placeholders', function () {
    CoreConfig::create(['code' => 'emails.configure.customer_messages.order_received_message', 'channel_code' => $this->order->channel->code, 'locale_code' => 'fr', 'value' => 'Bonjour :customer — :order <script>alert(1)</script>']);
    $html = (new CreatedNotification($this->order))->locale('fr')->render();
    expect($html)->toContain('Bonjour Amira Test', '&lt;script&gt;')->not->toContain('<script>');
});

it('includes the delivery address and the payment title configured in admin', function () {
    $this->order->addresses()->create(['address_type' => 'order_shipping', 'first_name' => 'Amira', 'last_name' => 'Test', 'address' => '10 rue de test', 'city' => 'Sousse', 'country' => 'TN']);
    $this->order->payment()->create(['method' => 'cashondelivery']);
    $html = (new CreatedNotification($this->order))->locale('fr')->render();
    expect($html)->toContain('10 rue de test', 'Sousse', core()->getConfigData('sales.payment_methods.cashondelivery.title'));
});

it('honors the confirmation notification switch', function () {
    CoreConfig::create(['code' => 'emails.general.notifications.emails.general.notifications.confirm_order', 'value' => '0']);
    app(OrderListener::class)->afterConfirmed($this->order);
    Mail::assertNotQueued(ConfirmedNotification::class);
});

it('honors the newsletter welcome notification switch', function () {
    CoreConfig::create(['code' => 'emails.general.notifications.emails.general.notifications.newsletter_subscription', 'value' => '0']);
    $subscriber = SubscribersList::create(['email' => 'newsletter@example.test', 'channel_id' => 1, 'is_subscribed' => 1]);
    app(Customer::class)->afterSubscribed($subscriber);
    Mail::assertNothingQueued();
});

it('does not queue a receipt from a rolled back transaction', function () {
    DB::beginTransaction();
    DB::afterCommit(fn () => app(OrderListener::class)->afterConfirmed($this->order));
    DB::rollBack();
    Mail::assertNothingQueued();
});

it('queues one newsletter welcome with an unsubscribe link and the subscriber language', function () {
    $subscriber = SubscribersList::create(['email' => 'newsletter@example.test', 'channel_id' => 1, 'is_subscribed' => 1, 'token' => str_repeat('a', 64)]);
    app()->setLocale('fr');
    app(Customer::class)->afterSubscribed($subscriber);
    Mail::assertQueued(SubscriptionNotification::class, fn ($mail) => $mail->locale === 'fr');
    expect((new SubscriptionNotification($subscriber))->locale('fr')->render())->toContain(route('shop.subscription.destroy', $subscriber->token));
});

it('excludes inactive guest subscriptions from newsletter campaign recipients', function () {
    SubscribersList::create(['email' => 'inactive@example.test', 'channel_id' => 1, 'is_subscribed' => 0]);
    SubscribersList::create(['email' => 'active@example.test', 'channel_id' => 1, 'is_subscribed' => 1]);
    $campaign = new Campaign;
    $campaign->setRelation('customer_group', CustomerGroup::where('code', 'guest')->firstOrFail());
    $recipients = app(Webkul\Marketing\Helpers\Campaign::class)->getEmailAddresses($campaign);
    expect($recipients)->toContain('active@example.test')->not->toContain('inactive@example.test');
});
