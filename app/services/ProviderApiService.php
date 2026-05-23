<?php
/**
 * Provider API Service
 * Handles communication with external SMM panel providers
 * Supports standard SMM panel API format (order, status, refill, cancel)
 */

namespace App\Services;

use App\Core\Logger;

class ProviderApiService
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct(string $apiUrl, string $apiKey)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function getBalance(): ?float
    {
        $response = $this->request(['action' => 'balance']);
        return isset($response['balance']) ? (float) $response['balance'] : null;
    }

    public function getServices(): ?array
    {
        return $this->request(['action' => 'services']);
    }

    public function placeOrder(string $serviceId, string $link, int $quantity, array $extras = []): ?array
    {
        $params = array_merge([
            'action' => 'add',
            'service' => $serviceId,
            'link' => $link,
            'quantity' => $quantity,
        ], $extras);

        return $this->request($params);
    }

    public function placeDripFeedOrder(string $serviceId, string $link, int $quantity, int $runs, int $interval): ?array
    {
        return $this->request([
            'action' => 'add',
            'service' => $serviceId,
            'link' => $link,
            'quantity' => $quantity,
            'runs' => $runs,
            'interval' => $interval,
        ]);
    }

    public function getOrderStatus(string $orderId): ?array
    {
        return $this->request([
            'action' => 'status',
            'order' => $orderId,
        ]);
    }

    public function getMultiOrderStatus(array $orderIds): ?array
    {
        return $this->request([
            'action' => 'status',
            'orders' => implode(',', $orderIds),
        ]);
    }

    public function requestRefill(string $orderId): ?array
    {
        return $this->request([
            'action' => 'refill',
            'order' => $orderId,
        ]);
    }

    public function getRefillStatus(string $refillId): ?array
    {
        return $this->request([
            'action' => 'refill_status',
            'refill' => $refillId,
        ]);
    }

    public function cancelOrder(string $orderId): ?array
    {
        return $this->request([
            'action' => 'cancel',
            'order' => $orderId,
        ]);
    }

    private function request(array $params): ?array
    {
        $params['key'] = $this->apiKey;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Logger::error("Provider API cURL error: {$error}", ['url' => $this->apiUrl]);
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::error("Provider API invalid response", ['response' => substr($response, 0, 500)]);
            return null;
        }

        if (isset($data['error'])) {
            Logger::warning("Provider API error: {$data['error']}", $params);
        }

        return $data;
    }
}
