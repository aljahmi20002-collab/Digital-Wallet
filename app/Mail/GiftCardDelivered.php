<?php

namespace App\Mail;

use App\Models\GiftCard;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GiftCardDelivered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public GiftCard $giftCard) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __(':sender sent you a gift card', ['sender' => $this->giftCard->sender_name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.gift-card.delivered',
            with: [
                'giftCard'   => $this->giftCard,
                'previewUrl' => route('gift-card.preview', $this->giftCard->code),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
