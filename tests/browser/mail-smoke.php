<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Mail;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Models\SubscribersList;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Shop\Mail\Customer\SubscriptionNotification;
use Webkul\Shop\Mail\Order\ConfirmedNotification;
use Webkul\Shop\Mail\Order\CreatedNotification;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (DB::connection()->getDatabaseName() !== 'hero_carousel_test' || getenv('MAIL_SMOKE_ALLOW_WRITES') !== '1') {
    throw new RuntimeException('This smoke test requires the disposable database and explicit write permission.');
}
config(['mail.default' => 'bagisto-dynamic-smtp', 'queue.default' => 'sync', 'cache.default' => 'array', 'responsecache.cache_store' => 'array']);
DB::beginTransaction();
try {
    foreach (['host' => '127.0.0.1', 'port' => '2527', 'encryption' => 'none', 'username' => '', 'password' => ''] as $name => $value) {
        CoreConfig::where('code', 'emails.configure.smtp.'.$name)->delete();
        CoreConfig::create(['code' => 'emails.configure.smtp.'.$name, 'value' => $value]);
    }
    foreach (['sender_email' => 'shop@example.test', 'sender_name' => 'Louzir Mode Intime', 'contact_email' => 'support@example.test'] as $name => $value) {
        CoreConfig::where('code', 'emails.configure.email_settings.'.$name)->delete();
        CoreConfig::create(['code' => 'emails.configure.email_settings.'.$name, 'value' => $value, 'channel_code' => core()->getCurrentChannel()->code]);
    }
    $order = Order::factory()->create(['customer_email' => 'customer@example.test', 'customer_first_name' => 'Amira', 'customer_last_name' => 'Test', 'is_guest' => 1, 'order_currency_code' => 'TND', 'sub_total' => 100, 'sub_total_incl_tax' => 100, 'shipping_amount' => 8, 'shipping_amount_incl_tax' => 8, 'grand_total' => 108, 'shipping_title' => 'Livraison normale']);
    OrderItem::factory()->create(['order_id' => $order->id, 'name' => 'Parure de test', 'sku' => 'MAIL-TEST', 'type' => 'simple', 'price' => 100, 'total' => 100, 'additional' => ['locale' => 'fr', 'attributes' => [['attribute_name' => 'Taille', 'option_label' => 'M']]]]);
    $order->payment()->create(['method' => 'cashondelivery']);
    $order->addresses()->create(['address_type' => 'order_shipping', 'first_name' => 'Amira', 'last_name' => 'Test', 'address' => '10 rue de test', 'city' => 'Sousse', 'state' => 'Sousse', 'country' => 'TN', 'postcode' => '4000', 'phone' => '20123456']);
    $subscriber = SubscribersList::create(['email' => 'subscriber@example.test', 'channel_id' => 1, 'is_subscribed' => 1, 'token' => str_repeat('t', 64)]);
    foreach (['fr', 'en', 'ar'] as $locale) {
        foreach (['received' => new CreatedNotification($order), 'confirmed' => new ConfirmedNotification($order), 'newsletter' => new SubscriptionNotification($subscriber)] as $kind => $mail) {
            $mail->locale($locale);
            file_put_contents(storage_path('framework/testing/mail-'.$kind.'-'.$locale.'.html'), $mail->render());
            if ($locale === 'fr') {
                Mail::send($mail);
            }
        }
    }
    echo "SMTP smoke: receipt, confirmation and newsletter delivered to local sink; nine localized previews rendered.\n";
} finally {
    DB::rollBack();
}
