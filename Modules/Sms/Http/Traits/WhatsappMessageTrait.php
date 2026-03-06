<?php

namespace Modules\Sms\Http\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Sms\Entities\SmsTemplateId;

trait WhatsappMessageTrait
{
    public function toWhatsapp($slug, $notifiable, $message, $data = [])
    {
        foreach ($data as $key => $value) {
            if (! is_string($value)) {
                $data[$key] = strval($value);
            }
        }
        $settings = sms_setting();
        $this->smsTemplateId = SmsTemplateId::where('sms_setting_slug', $slug)->first();

        if (! $settings->whatsapp_status) {
            return true;
        }

        if (! $this->smsTemplateId || ! $this->smsTemplateId->whatsapp_template_sid) {
            Log::warning("Twilio WhatsApp Message Skipped: Missing WhatsApp Template SID for slug '$slug'");

            return true;
        }

        try {
            $countryCode = str_replace('+', '', $notifiable->country_phonecode);
            // Remove leading zero from mobile if exists, since we are prepending country code
            $mobile = ltrim($notifiable->mobile, '0');
            $toNumber = $countryCode.$mobile;
            if (! str_starts_with($toNumber, '+')) {
                $toNumber = '+'.$toNumber;
            }

            $fromNumber = $settings->whatapp_from_number;
            if (! str_starts_with($fromNumber, '+')) {
                $fromNumber = '+'.$fromNumber;
            }

            $twilio = new \Twilio\Rest\Client($settings->account_sid, $settings->auth_token);

            $message = $twilio->messages
                ->create(
                    'whatsapp:'.$toNumber, // to
                    [
                        'from' => 'whatsapp:'.$fromNumber,
                        'body' => $message,
                        'contentSid' => $this->smsTemplateId->whatsapp_template_sid,
                        'ContentVariables' => json_encode($data),
                    ]
                );
            Log::info('Twilio WhatsApp Message Sent Successfully to '.$toNumber);
        } catch (Exception $e) {
            Log::error('Twilio WhatsApp Message Failed: '.$e->getMessage());
        }
    }
}
