<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FinikService
{
    private string $baseUrl;
    private string $host;
    private string $apiKey;
    private string $accountId;
    private string $privateKey;

    public function __construct()
    {
        $this->baseUrl = env('FINIK_ENV') === 'production'
            ? 'https://api.acquiring.averspay.kg'
            : 'https://beta.api.acquiring.averspay.kg';

        $this->host = parse_url($this->baseUrl, PHP_URL_HOST);
        $this->apiKey = env('FINIK_API_KEY');
        $this->accountId = env('FINIK_ACCOUNT_ID');

        $privateKeyPath = base_path(env('FINIK_PRIVATE_KEY_PATH'));
        $this->privateKey = file_get_contents($privateKeyPath);
    }

    public function createPayment(Order $order, string $token): array
    {
        $path = '/v1/payment';
        $timestamp = (string) round(microtime(true) * 1000);

        $paymentId = (string) Str::uuid();

        $body = [
            'Amount' => (float) $order->total,
            'CardType' => 'FINIK_QR',
            'PaymentId' => $paymentId,
            'RedirectUrl' => route('orders.qr-pay', [$order, $token]),
            'Data' => [
                'accountId' => $this->accountId,
                'name_en' => env('FINIK_QR_NAME', 'Library payment'),
                'description' => 'Оплата заказа #' . $order->id,
                'webhookUrl' => route('finik.webhook'),
                'additionalData' => [
                    [
                        'fieldId' => 'order_id',
                        'name' => 'Order ID',
                        'isHidden' => true,
                        'value' => (string) $order->id,
                    ],
                    [
                        'fieldId' => 'access_token',
                        'name' => 'Access Token',
                        'isHidden' => true,
                        'value' => $token,
                    ],
                ],
            ],
        ];

        $jsonBody = $this->jsonEncodeSorted($body);

        $headersForSign = [
            'host' => $this->host,
            'x-api-key' => $this->apiKey,
            'x-api-timestamp' => $timestamp,
        ];

        $signature = $this->sign('post', $path, $headersForSign, null, $jsonBody);

        $response = Http::withHeaders([
                'content-type' => 'application/json',
                'x-api-key' => $this->apiKey,
                'x-api-timestamp' => $timestamp,
                'signature' => $signature,
            ])
            ->withoutRedirecting()
            ->withBody($jsonBody, 'application/json')
            ->post($this->baseUrl . $path);

        if ($response->status() === 302) {
            return [
                'payment_id' => $paymentId,
                'payment_url' => $response->header('Location'),
                'request_payload' => $jsonBody,
                'response_payload' => null,
            ];
        }

        if ($response->successful()) {
            $data = $response->json();

            return [
                'payment_id' => $paymentId,
                'payment_url' => $data['paymentUrl'] ?? null,
                'request_payload' => $jsonBody,
                'response_payload' => $response->body(),
            ];
        }

\Log::info('FINIK DEBUG', [
    'url' => $this->baseUrl . $path,
    'accountId' => $this->accountId,
    'status' => $response->status(),
    'body' => $response->body(),
    'headers' => $response->headers(),
]);


        throw new \RuntimeException('Finik error: ' . $response->status() . ' ' . $response->body());
    }

    private function sign(string $method, string $path, array $headers, ?array $query, string $jsonBody): string
    {
        ksort($headers);

        $headerParts = [];

        foreach ($headers as $key => $value) {
            $headerParts[] = strtolower($key) . ':' . $value;
        }

        $canonical = strtolower($method) . "\n";
        $canonical .= $path . "\n";
        $canonical .= implode('&', $headerParts) . "\n";

        if ($query && count($query)) {
            ksort($query);

            $queryParts = [];

            foreach ($query as $key => $value) {
                $queryParts[] = rawurlencode($key) . '=' . rawurlencode((string) $value);
            }

            $canonical .= implode('&', $queryParts) . "\n";
        }

        $canonical .= $jsonBody;

        openssl_sign($canonical, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    private function jsonEncodeSorted(array $data): string
    {
        $sorted = $this->sortRecursive($data);

        return json_encode($sorted, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function sortRecursive(array $data): array
    {
        ksort($data);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->isList($value)
                    ? array_map(fn ($item) => is_array($item) ? $this->sortRecursive($item) : $item, $value)
                    : $this->sortRecursive($value);
            }
        }

        return $data;
    }

    private function isList(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }
}
