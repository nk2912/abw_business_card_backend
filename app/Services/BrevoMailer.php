<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class BrevoMailer
{
    public function sendOtp(string $email, int $otp): void
    {
        $apiKey = (string) config('services.brevo.key');
        $senderEmail = (string) config('services.brevo.sender_email');
        $senderName = (string) config('services.brevo.sender_name');
        $baseUrl = rtrim((string) config('services.brevo.base_url'), '/');
        $timeout = (int) config('services.brevo.timeout', 30);

        if ($apiKey === '') {
            throw new \RuntimeException('BREVO_API_KEY is not configured.');
        }

        if ($senderEmail === '') {
            throw new \RuntimeException('BREVO_FROM_EMAIL is not configured.');
        }

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'accept' => 'application/json',
        ])
            ->timeout($timeout)
            ->post($baseUrl.'/smtp/email', [
                'sender' => [
                    'name' => $senderName !== '' ? $senderName : 'BusinessCard4U',
                    'email' => $senderEmail,
                ],
                'to' => [
                    ['email' => $email],
                ],
                'subject' => 'BusinessCard4U OTP Code',
                'textContent' => "Your BusinessCard4U verification code is: {$otp}",
            ]);

        if ($response->failed()) {
            throw new RequestException($response);
        }
    }
}
