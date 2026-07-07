<?php

namespace App\Http\Controllers\Webhook;

use App\Actions\Payment\HandleDokuWebhook;
use App\Http\Controllers\Controller;
use App\Services\Payment\DokuClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thin controller (Action-Oriented): verifikasi signature lalu delegasikan ke Action.
 * CSRF di-bypass via bootstrap/app.php (webhooks/*) — keamanan dari HMAC signature.
 */
class DokuWebhookController extends Controller
{
    public function __invoke(Request $request, DokuClient $client, HandleDokuWebhook $action): JsonResponse
    {
        abort_unless($client->isValidWebhook($request), 401, 'Invalid webhook signature.');

        $payment = $action->execute($request->json()->all());

        abort_unless($payment, 404, 'Unknown invoice number.');

        return response()->json(['message' => 'OK']);
    }
}
