<?php

namespace App\Notifications\User;

use App\Enums\NotificationActionType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomMessageNotify extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param array<int, string> $via
     */
    public function __construct(
        private string $message,
        private array $via,
        private ?string $subject = null,
        private ?string $actionUrl = null,
        private ?string $actionText = null,
    ) {
        $this->subject    ??= 'Notification from Admin';
        $this->actionText ??= 'View Notification';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'notificationDeliveryEnabled') && ! $notifiable->notificationDeliveryEnabled()) {
            return [];
        }

        return $this->via;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject($this->subject ?? 'Notification from Admin')
            ->line($this->message);

        if (filled($this->actionUrl)) {
            $mailMessage->action($this->actionText ?? 'View Notification', $this->actionUrl);
        }

        return $mailMessage->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'       => $this->subject ?? '',
            'message'     => $this->message,
            'icon'        => 'user-info-1',
            'action_type' => NotificationActionType::CREATED->value,
            'action_link' => $this->actionUrl ?? '',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage(
            [
                'title'       => $this->subject ?? '',
                'message'     => $this->message,
                'icon'        => 'user-info-1',
                'action_type' => NotificationActionType::CREATED->value,
                'action_link' => $this->actionUrl ?? '',
                'timestamp'   => now()->toISOString(),
            ]
        );
    }
}
