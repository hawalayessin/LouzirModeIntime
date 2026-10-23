<?php

use Webkul\Payment\Payment\CashOnDelivery;

return [
    'cashondelivery' => [
        'class' => CashOnDelivery::class,
        'code' => 'cashondelivery',
        'title' => 'Paiement à la livraison',
        'description' => 'Paiement à la livraison',
        'active' => true,
        'generate_invoice' => false,
        'sort' => 6,
    ],

];
