<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrevoMailer
{
    public function sendOtp(string $email, int $otp): void
    {
        $apiKey = trim((string) config('services.brevo.key'));
        $senderEmail = trim((string) config('services.brevo.sender_email'));
        $senderName = trim((string) config('services.brevo.sender_name'));
        $baseUrl = rtrim(trim((string) config('services.brevo.base_url')), '/');
        $timeout = (int) config('services.brevo.timeout', 30);

        if ($apiKey === '') {
            throw new RuntimeException('BREVO_API_KEY is not configured.');
        }

        if ($senderEmail === '') {
            throw new RuntimeException('BREVO_FROM_EMAIL is not configured.');
        }

        try {
            Http::withHeaders([
                'api-key' => $apiKey,
                'accept' => 'application/json',
            ])
                ->connectTimeout(10)
                ->timeout($timeout)
                ->post($baseUrl.'/smtp/email', [
                    'sender' => [
                        'name' => $senderName !== '' ? $senderName : 'BusinessCard4U',
                        'email' => $senderEmail,
                    ],
                    'to' => [
                        ['email' => trim($email)],
                    ],
                    'subject' => 'BusinessCard4U OTP Code',
                    'textContent' => "Your BusinessCard4U verification code is: {$otp}",
                ])
                ->throw();
        } catch (RequestException $e) {
            $response = $e->response;
            $status = $response?->status();
            $body = $response?->body();

            throw new RuntimeException(
                'Brevo send failed'
                .($status ? " [HTTP {$status}]" : '')
                .($body ? " {$body}" : ''),
                previous: $e
            );
        }
    }
}
