<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Klien DOKU Checkout (Jokul).
 * Semua request ditandatangani HMAC-SHA256 atas komponen:
 * Client-Id, Request-Id, Request-Timestamp, Request-Target, dan Digest (SHA256 body).
 * Skema yang sama dipakai terbalik untuk memverifikasi webhook masuk.
 */
class DokuClient
{
    private const CHECKOUT_TARGET = '/checkout/v1/payment';

    private string $clientId;

    private string $secretKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->clientId = (string) config('services.doku.client_id');
        $this->secretKey = (string) config('services.doku.secret_key');
        $this->baseUrl = rtrim((string) config('services.doku.base_url'), '/');
    }

    /** Kredensial belum diisi = mode dev; caller wajib fallback dengan anggun */
    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->secretKey !== '';
    }

    /**
     * Buat sesi DOKU Checkout untuk sebuah invoice; return URL halaman bayar.
     */
    public function createCheckoutSession(Payment $payment): string
    {
        $body = json_encode([
            'order' => [
                'amount' => (int) $payment->amount,
                'invoice_number' => $payment->external_id,
                // Tujuan redirect user setelah menyelesaikan pembayaran di halaman DOKU
                'callback_url' => route('user.packages', ['status' => 'payment-return']),
            ],
            'payment' => [
                'payment_due_date' => 60, // Menit — selaras dengan status `expired`
            ],
            'customer' => [
                'name' => $payment->user->name,
                'email' => $payment->user->email,
            ],
        ]);

        $requestId = (string) Str::uuid();
        $timestamp = now('UTC')->format('Y-m-d\TH:i:s\Z');

        $response = Http::withHeaders([
            'Client-Id' => $this->clientId,
            'Request-Id' => $requestId,
            'Request-Timestamp' => $timestamp,
            'Signature' => $this->signature($requestId, $timestamp, self::CHECKOUT_TARGET, $body),
        ])
            ->withBody($body, 'application/json')
            ->post($this->baseUrl.self::CHECKOUT_TARGET)
            ->throw();

        $url = $response->json('response.payment.url');

        if (! $url) {
            throw new RuntimeException('DOKU tidak mengembalikan payment URL untuk invoice '.$payment->external_id);
        }

        return $url;
    }

    /**
     * Verifikasi webhook masuk: hitung ulang signature dari raw body + headers
     * dan bandingkan timing-safe dengan header Signature kiriman DOKU.
     */
    public function isValidWebhook(Request $request): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        if ((string) $request->header('Client-Id') !== $this->clientId) {
            return false;
        }

        $expected = $this->signature(
            (string) $request->header('Request-Id'),
            (string) $request->header('Request-Timestamp'),
            '/'.ltrim($request->path(), '/'),
            $request->getContent(),
        );

        return hash_equals($expected, (string) $request->header('Signature'));
    }

    /**
     * Komposisi signature sesuai spesifikasi DOKU (urutan & newline signifikan).
     */
    public function signature(string $requestId, string $timestamp, string $requestTarget, string $body): string
    {
        $digest = base64_encode(hash('sha256', $body, true));

        $raw = implode("\n", [
            'Client-Id:'.$this->clientId,
            'Request-Id:'.$requestId,
            'Request-Timestamp:'.$timestamp,
            'Request-Target:'.$requestTarget,
            'Digest:'.$digest,
        ]);

        return 'HMACSHA256='.base64_encode(hash_hmac('sha256', $raw, $this->secretKey, true));
    }
}
