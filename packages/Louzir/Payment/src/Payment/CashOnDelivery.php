<?php

namespace Webkul\Payment\Payment;

use Illuminate\Support\Facades\Storage;

class CashOnDelivery extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'cashondelivery';

    /**
     * Get redirect url.
     *
     * @return string
     */
    public function getRedirectUrl() {}

    /**
     * Get the localized payment method title.
     */
    public function getTitle()
    {
        return trans('shop::app.checkout.onepage.payment.cash-on-delivery.title');
    }

    /**
     * Get the localized payment method description.
     */
    public function getDescription()
    {
        return trans('shop::app.checkout.onepage.payment.cash-on-delivery.description');
    }

    /**
     * Is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        if (! $this->cart) {
            $this->setCart();
        }

        return $this->getConfigData('active') && $this->cart?->hasOnlyStockableItems();
    }

    /**
     * Get payment method image.
     *
     * @return array
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/cash-on-delivery.png', 'shop');
    }
}
