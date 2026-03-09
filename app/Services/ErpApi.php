<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ErpApi
{
    private function client()
    {
        return Http::timeout(10)->withHeaders([
            'X-ERP-KEY' => env('ERP_SYNC_KEY'),
            'Accept' => 'application/json',
        ]);
    }

    public function upsertChauffer(array $data)
    {
        $this->client()->post(env('ERP_API_BASE') . '/sync/chauffers', $data)->throw();
    }

    public function deleteChauffer(string $phone)
    {
        $this->client()->delete(env('ERP_API_BASE') . '/sync/chauffers/' . urlencode($phone))->throw();
    }

    public function createTransport(array $payload)
    {
        $this->client()->post(env('ERP_API_BASE') . '/sync/transport-services', $payload)->throw();
    }

    public function updateTransport(int $id, array $payload)
    {
        $this->client()->put(env('ERP_API_BASE') . "/sync/transport-services/{$id}", $payload)->throw();
    }

    public function deleteTransport(int $id)
    {
        $this->client()->delete(env('ERP_API_BASE') . "/sync/transport-services/{$id}")->throw();
    }


    public function createTransport1(array $payload)
    {
        $payload['employee_id'] = (string) $payload['employee_id']; // force string
        $this->client()
            ->post(env('ERP_API_BASE') . '/sync/transport-services', $payload)
            ->throw();
    }

    public function updateTransport1(int $id, array $payload)
    {
        $payload['employee_id'] = (string) $payload['employee_id'];
        $this->client()
            ->put(env('ERP_API_BASE') . "/sync/transport-services/{$id}", $payload)
            ->throw();
    }
}