<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case Shipped = 'shipped';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Order Placed',
            self::Preparing => 'Preparing & Cutting',
            self::Shipped => 'Out for Delivery / Ready for Pickup',
            self::Received => 'Delivered / Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function notificationMessage(): string
    {
        return match ($this) {
            self::Pending => 'Order placed and waiting for shop confirmation.',
            self::Preparing => 'The butcher is currently preparing and cutting your order.',
            self::Shipped => 'Your order is on the way with our rider or ready for pickup.',
            self::Received => 'Your order has been successfully delivered and completed.',
            self::Cancelled => 'This order has been cancelled.',
        };
    }
}
