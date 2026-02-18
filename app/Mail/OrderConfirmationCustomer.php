<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $cart;

    public function __construct(Order $order, array $cart)
    {
        $this->order = $order;
        $this->cart = $cart;
    }

    public function build()
    {
        return $this->subject('Votre commande NutriSport')
                    ->view('emails.order-customer');
    }
}