<?php

namespace App\Jobs;

use App\Services\Sms\SmsGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOtpSmsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $phone,
        private readonly string $code,
    ) {
    }

    public function handle(SmsGateway $gateway): void
    {
        $gateway->send($this->phone, "Votre code de vérification Ticketrama est : {$this->code}");
    }
}
