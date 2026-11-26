<?php

namespace App\Http\Controllers;

use App\Services\Paiement\FedaPayClient;
use App\Services\Paiement\PaiementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FedaPayWebhookController extends Controller
{
    public function __invoke(Request $request, FedaPayClient $client, PaiementService $paiement): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-FedaPay-Signature');

        try {
            $event = $client->verifierWebhook($payload, $signature);
        } catch (\Throwable $e) {
            Log::warning('Webhook FedaPay rejeté : signature invalide', ['ip' => $request->ip()]);

            abort(403);
        }

        $data = $event->data ?? null;
        $transactionId = is_array($data) ? ($data['id'] ?? null) : ($data->id ?? null);

        if ($transactionId) {
            // Le service relit l'état réel de la transaction avant de confirmer
            $paiement->confirmer((string) $transactionId);
        }

        Log::info('Webhook FedaPay traité', ['type' => $event->name ?? $event->type ?? null]);

        return response()->json(['ok' => true]);
    }
}
