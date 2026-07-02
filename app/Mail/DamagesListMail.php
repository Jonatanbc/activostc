<?php

namespace App\Mail;

use App\Models\AssetDamage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DamagesListMail extends BaseMailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly bool $onlyPending = false) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: trans('admin/damages/general.all_damages'),
        );
    }

    public function content(): Content
    {
        $query = AssetDamage::with('asset.model', 'asset.assignedTo', 'asset.location', 'damageType', 'supplier')
            ->orderByDesc('id');

        if ($this->onlyPending) {
            $query->whereIn('status', AssetDamage::PENDING_STATUSES);
        }

        $damages = $query->get();

        return new Content(
            view: 'emails.damages-list',
            with: [
                'damages' => $damages,
                'totalCost' => $damages->sum('cost'),
                'onlyPending' => $this->onlyPending,
                'generatedAt' => now(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
