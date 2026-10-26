<?php

namespace Webkul\Shop\Mail\Order;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ConfirmedNotification extends CreatedNotification
{
    public function envelope(): Envelope
    {
        $envelope = parent::envelope();
        $envelope->subject = trans('shop::order-mail.confirmed-subject', ['number' => $this->order->increment_id]);

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(view: 'shop::emails.orders.created', with: ['confirmed' => true]);
    }
}
