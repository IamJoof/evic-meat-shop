<?php

namespace App\Notifications;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportReplyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SupportConversation $conversation,
        public SupportMessage $message
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $accessUrl = url('/?support_token='.$this->conversation->access_token);

        return (new MailMessage)
            ->subject('Evic Meat Shop: Response to your inquiry')
            ->greeting('Hello '.$this->conversation->customer_name.',')
            ->line('Our butcher counter staff has responded to your message:')
            ->line('“'.$this->message->message.'”')
            ->action('View & Reply in Storefront Chat', $accessUrl)
            ->line('You can continue the conversation directly through the link above at any time.')
            ->line('Thank you for choosing Evic Meat & Poultry Shop!');
    }
}
