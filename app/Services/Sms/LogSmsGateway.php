<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGateway
{
    public function send(string $phone, string $message): void
    {
        Log::channel(config('logging.default'))->info('[OTP SMS]', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}
