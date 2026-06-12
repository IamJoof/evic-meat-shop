<?php

namespace App\Enums;

enum OrderStatus : string
{
    case Received = 'received';
    case Shipped = 'shipped';
    case Refunded = 'refund';
    case Cancelled = 'cancelled';

    public function notificationMessage(): string
    {
        return match($this){
            self::Received => 'We are currently preparing your order.',
            self::Shipped => 'Your order is on your way. Please wait for the rider to deliver your order',
            self::Refunded => 'We are done processing the refund. Please check your balance',
            self::Cancelled => 'We apologize for the inconvenience. Your order is cancelled'
        };
    }
}
