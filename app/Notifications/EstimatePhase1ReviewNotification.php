<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Estimate;
use Illuminate\Notifications\Messages\MailMessage;

class EstimatePhase1ReviewNotification extends BaseNotification
{
    public function __construct(
        private readonly Estimate $estimate,
        private readonly string $event,
    ) {
        $this->company = $this->estimate->company;
    }

    /**
     * @param  mixed  $notifiable
     * @return list<string>
     */
    public function via($notifiable): array
    {
        $via = ['database'];

        if ($notifiable->email_notifications && $notifiable->email !== '') {
            $via[] = 'mail';
        }

        return $via;
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $build = parent::build($notifiable);
        $url = getDomainSpecificUrl(route('estimates.show', $this->estimate->id), $this->company);

        $build
            ->subject(__('email.estimatePhase1Review.subject', [
                'number' => $this->estimate->estimate_number,
                'event' => __('email.estimatePhase1Review.events.'.$this->event),
            ]))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => __('email.estimatePhase1Review.text', [
                    'number' => $this->estimate->estimate_number,
                    'event' => __('email.estimatePhase1Review.events.'.$this->event),
                ]),
                'themeColor' => $this->company->header_color,
                'actionText' => __('email.estimatePhase1Review.action'),
                'notifiableName' => $notifiable->name,
            ]);

        parent::resetLocale();

        return $build;
    }

    /**
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'id' => $this->estimate->id,
            'estimate_number' => $this->estimate->estimate_number,
            'event' => $this->event,
            'url' => route('estimates.show', $this->estimate->id),
        ];
    }
}
