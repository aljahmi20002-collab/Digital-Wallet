<?php

declare(strict_types=1);

namespace App\Notifications\P2P;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class P2POrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $orderId,
        public readonly string $title,
        public readonly string $message,
        public readonly array $data = []
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'p2p_order',
            'orderId' => $this->orderId,
            'title'   => $this->title,
            'message' => $this->message,
            'data'    => $this->data,
        ];
    }
}
