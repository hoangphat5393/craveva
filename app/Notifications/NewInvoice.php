<?php

namespace App\Notifications;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Models\EmailNotificationSetting;
use App\Models\GlobalSetting;
use App\Models\Invoice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use NotificationChannels\OneSignal\OneSignalChannel;

class NewInvoice extends BaseNotification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $invoice;

    private $emailSetting;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->company = $this->invoice->company;
        $this->emailSetting = EmailNotificationSetting::where('company_id', $this->company->id)->where('slug', 'invoice-createupdate-notification')->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ($this->emailSetting->send_email == 'yes' && $notifiable->email_notifications && $notifiable->email != '') ? ['mail', 'database'] : ['database'];

        if ($this->emailSetting->send_push == 'yes' && push_setting()->status == 'active') {
            array_push($via, OneSignalChannel::class);
        }

        if ($this->emailSetting->send_slack == 'yes' && $this->company->slackSetting->status == 'active') {
            $this->slackUserNameCheck($notifiable) ? array_push($via, 'slack') : null;
        }

        if ($this->emailSetting->send_push == 'yes' && push_setting()->beams_push_status == 'active') {
            $pushNotification = new DashboardController;
            $pushUsersIds = [[$notifiable->id]];
            $pushNotification->sendPushNotifications($pushUsersIds, __('email.invoice.subject'), $this->invoice->invoice_number);
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage|void
     */
    public function toMail($notifiable)
    {
        // #region agent log
        @file_put_contents(
            base_path('debug-0fea0f.log'),
            json_encode([
                'sessionId' => '0fea0f',
                'runId' => 'initial',
                'hypothesisId' => 'H3',
                'location' => 'NewInvoice.php:toMail:entry',
                'message' => 'NewInvoice mail rendering started',
                'data' => [
                    'invoiceId' => (int) ($this->invoice->id ?? 0),
                    'invoiceNumber' => (string) ($this->invoice->invoice_number ?? ''),
                    'notifiableId' => (int) ($notifiable->id ?? 0),
                ],
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_UNICODE).PHP_EOL,
            FILE_APPEND
        );
        // #endregion

        $newInvoice = parent::build($notifiable);

        if (($this->invoice->project && ! is_null($this->invoice->project->client)) || ! is_null($this->invoice->client_id)) {
            // For sending invoice PDF attachment when dompdf is healthy.
            $invoiceController = new InvoiceController;
            $pdfOption = null;

            try {
                $pdfOption = $invoiceController->domPdfObjectForDownload($this->invoice->id);
            } catch (\Throwable $e) {
                // #region agent log
                @file_put_contents(
                    base_path('debug-0fea0f.log'),
                    json_encode([
                        'sessionId' => '0fea0f',
                        'runId' => 'post-fix',
                        'hypothesisId' => 'H4',
                        'location' => 'NewInvoice.php:toMail:dompdf-exception',
                        'message' => 'dompdf failed, fallback to email without attachment',
                        'data' => [
                            'invoiceId' => (int) ($this->invoice->id ?? 0),
                            'exceptionClass' => get_class($e),
                            'exceptionMessage' => (string) $e->getMessage(),
                        ],
                        'timestamp' => (int) round(microtime(true) * 1000),
                    ], JSON_UNESCAPED_UNICODE).PHP_EOL,
                    FILE_APPEND
                );
                // #endregion
            }

            if ($pdfOption) {
                $pdf = $pdfOption['pdf'];
                $filename = $pdfOption['fileName'];
                try {
                    $newInvoice->attachData($pdf->output(), $filename.'.pdf');
                } catch (\Throwable $e) {
                    // #region agent log
                    @file_put_contents(
                        base_path('debug-0fea0f.log'),
                        json_encode([
                            'sessionId' => '0fea0f',
                            'runId' => 'post-fix-3',
                            'hypothesisId' => 'H8',
                            'location' => 'NewInvoice.php:toMail:pdf-output-exception',
                            'message' => 'dompdf output failed in NewInvoice, attachment skipped',
                            'data' => [
                                'invoiceId' => (int) ($this->invoice->id ?? 0),
                                'exceptionClass' => get_class($e),
                                'exceptionMessage' => (string) $e->getMessage(),
                            ],
                            'timestamp' => (int) round(microtime(true) * 1000),
                        ], JSON_UNESCAPED_UNICODE).PHP_EOL,
                        FILE_APPEND
                    );
                    // #endregion
                }
            }

            App::setLocale($notifiable->locale ?? $this->company->locale ?? 'en');

            $url = url()->temporarySignedRoute('front.invoice', now()->addDays(GlobalSetting::SIGNED_ROUTE_EXPIRY), $this->invoice->hash);
            $url = getDomainSpecificUrl($url, $this->company);
            $content = __('email.invoice.text').'<br>'.__('app.invoiceNumber').': '.$this->invoice->invoice_number;

            $newInvoice->subject(__('email.invoice.subject').' ('.$this->invoice->invoice_number.') - '.config('app.name').'.')
                ->markdown('mail.email', [
                    'url' => $url,
                    'content' => $content,
                    'themeColor' => $this->company->header_color,
                    'actionText' => __('email.viewInvoice'),
                    'notifiableName' => $notifiable->name,
                ]);

            return $newInvoice;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    // phpcs:ignore
    public function toArray($notifiable)
    {
        return [
            'id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
        ];
    }

    public function toSlack($notifiable)
    {
        return $this->slackBuild($notifiable)
            ->content(__('email.hello').' '.$notifiable->name.' '.__('email.invoice.subject'));
    }
}
