<?php

namespace App\Mail;

use App\Models\AssetDamage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DamageReportMail extends BaseMailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly bool $onlyPending = false) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: trans('admin/damages/general.damages_by_model_matrix'),
        );
    }

    public function content(): Content
    {
        $matrix = AssetDamage::byModelMatrix($this->onlyPending);

        return new Content(
            view: 'emails.damages-by-model',
            with: array_merge($matrix, [
                'onlyPending' => $this->onlyPending,
                'generatedAt' => now(),
            ]),
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
