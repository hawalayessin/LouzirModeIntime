<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\Customer\Models\CustomerGroup;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (! app()->environment(['local', 'testing'])) {
    throw new RuntimeException('Responsive fixtures are restricted to local/test environments.');
}

$email = 'responsive-regression@example.invalid';
$customer = Customer::where('email', $email)->first();
$path = 'responsive-regression/avatar.svg';

if (($argv[1] ?? '') === 'cleanup') {
    if ($customer) {
        if (DB::table('orders')->where('customer_id', $customer->id)->exists()) {
            throw new RuntimeException('Unexpected order: refusing to remove this fixture.');
        }
        DB::table('cart')->where('customer_id', $customer->id)->delete();
        CustomerAddress::where('customer_id', $customer->id)->delete();
        $customer->delete();
    }
    Storage::delete($path);
    echo json_encode(['cleaned' => true]);
    exit;
}

if ($customer) {
    throw new RuntimeException('Clean the previous responsive fixture before creating another.');
}

$password = bin2hex(random_bytes(16));
$customer = Customer::create([
    'first_name' => 'Responsive', 'last_name' => 'Regression',
    'email' => $email, 'password' => Hash::make($password),
    'phone' => '22123456', 'gender' => 'Other', 'status' => 1, 'is_verified' => 1,
    'channel_id' => core()->getCurrentChannel()->id,
    'customer_group_id' => CustomerGroup::where('code', 'general')->value('id'),
]);
Storage::put($path, '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="160"><rect width="80" height="160" fill="#d7e5de"/><circle cx="40" cy="45" r="22" fill="#708b7e"/><path d="M5 160v-30a35 35 0 0 1 70 0v30" fill="#708b7e"/></svg>');
$customer->image = $path;
$customer->save();
echo json_encode(['email' => $email, 'password' => $password, 'id' => $customer->id]);
