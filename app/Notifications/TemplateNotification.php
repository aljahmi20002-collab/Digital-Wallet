<?php

namespace App\Notifications;

use App\Enums\NotificationChannelType;
use App\Models\NotificationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class TemplateNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        protected string $identifier,
        protected array $data = [],
        protected mixed $sender = null,
        protected mixed $action = null,
    ) {}

    public function via(object $notifiable): array
    {
        $pusherStatus = pluginCredentials('pusher')['status'] ?? false;
        $twilioStatus = pluginCredentials('twilio')['status'] ?? false;
        $pushEnabled  = ! method_exists($notifiable, 'notificationDeliveryEnabled')
            || $notifiable->notificationDeliveryEnabled();

        $template = NotificationTemplate::where('identifier', $this->identifier)
            ->with('channels')
            ->firstOrFail();

        return collect($template->channels)
            ->where('is_active', true)
            ->flatMap(function ($channel) use ($pusherStatus, $twilioStatus, $pushEnabled) {
                return match ($channel->channel) {
                    NotificationChannelType::EMAIL => ['mail'],
                    NotificationChannelType::SMS   => $twilioStatus ? [TwilioChannel::class] : [],
                    NotificationChannelType::PUSH  => $pushEnabled ? ($pusherStatus ? ['database', 'broadcast'] : ['database']) : [],
                    default                        => [],
                };
            })
            ->unique()
            ->toArray();
    }

    public function toMail(object $notifiable): ?MailMessage
    {
        $template = $this->getTemplate('email');
        if (! $template) {
            return null;
        }

        return (new MailMessage)
            ->subject($template['title'] ?? 'Notification')
            ->line(str_replace_placeholders($template['message'], $this->data));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $template = $this->getTemplate('push');
        if (! $template) {
            return [];
        }

        $base       = $this->getBase();
        $senderInfo = null;
        if ($this->sender) {
            $senderInfo = [
                'id'     => $this->sender->id,
                'name'   => $this->sender->name       ?? null,
                'avatar' => $this->sender->avatar_alt ?? null,
            ];
        }

        return [
            'title'       => $template['title'] ?? '',
            'message'     => str_replace_placeholders($template['message'] ?? '', $this->data),
            'icon'        => $base->icon,
            'action_type' => $base->action_type->value ?? '',
            'action_link' => $this->action             ?? '',
            'sender'      => $senderInfo,
        ];

    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->broadcastWith());
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $t = $this->getTemplate('push');

        return [
            'title'       => $t['title'] ?? '',
            'message'     => str_replace_placeholders($t['message'] ?? '', $this->data),
            'icon'        => $this->getBase()->icon,
            'action_type' => $this->getBase()->action_type->value,
            'action_link' => $this->action ?? '',
            'timestamp'   => now()->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.received';
    }

    public function toTwilio(object $notifiable): TwilioSmsMessage
    {
        $sms = $this->getTemplate('sms');

        return (new TwilioSmsMessage)
            ->content(str_replace_placeholders($sms['message'], $this->data));
    }

    protected function getBase(): NotificationTemplate
    {
        return NotificationTemplate::where('identifier', $this->identifier)->firstOrFail();
    }

    protected function getTemplate(string $channel): ?array
    {
        $base     = $this->getBase();
        $template = $base->channels()->where('channel', $channel)->where('is_active', true)->first();

        return $template ? $template->toArray() : null;
    }
}
