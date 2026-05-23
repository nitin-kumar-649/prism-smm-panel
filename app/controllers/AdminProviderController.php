<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Provider;
use App\Services\ProviderApiService;

class AdminProviderController extends Controller
{
    private Provider $providerModel;

    public function __construct()
    {
        parent::__construct();
        $this->providerModel = new Provider();
    }

    public function index(): void
    {
        $providers = $this->providerModel->all('id ASC', 100);

        $this->view('admin.providers', [
            'title' => 'API Providers',
            'providers' => $providers,
        ]);
    }

    public function create(): void
    {
        if (!$this->validateCsrf()) return;

        $this->providerModel->create([
            'name' => $this->input('name'),
            'api_url' => $this->input('api_url'),
            'api_key' => $_POST['api_key'] ?? '',
            'status' => $this->input('status', 'active'),
            'description' => $this->input('description'),
        ]);

        $this->json(['success' => true, 'message' => 'Provider added.']);
    }

    public function update(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $data = [
            'name' => $this->input('name'),
            'api_url' => $this->input('api_url'),
            'status' => $this->input('status', 'active'),
            'description' => $this->input('description'),
        ];

        $apiKey = $_POST['api_key'] ?? '';
        if (!empty($apiKey)) {
            $data['api_key'] = $apiKey;
        }

        $this->providerModel->update((int) $id, $data);
        $this->json(['success' => true, 'message' => 'Provider updated.']);
    }

    public function delete(string $id): void
    {
        $this->providerModel->delete((int) $id);
        $this->json(['success' => true, 'message' => 'Provider deleted.']);
    }

    public function checkBalance(string $id): void
    {
        $provider = $this->providerModel->find((int) $id);
        if (!$provider) {
            $this->json(['error' => 'Provider not found.'], 404);
            return;
        }

        $api = new ProviderApiService($provider['api_url'], $provider['api_key']);
        $balance = $api->getBalance();

        if ($balance !== null) {
            $this->providerModel->update((int) $id, ['balance' => $balance]);
            $this->json(['success' => true, 'balance' => $balance]);
        } else {
            $this->json(['error' => 'Could not fetch balance.'], 500);
        }
    }

    public function getServices(string $id): void
    {
        $provider = $this->providerModel->find((int) $id);
        if (!$provider) {
            $this->json(['error' => 'Provider not found.'], 404);
            return;
        }

        $api = new ProviderApiService($provider['api_url'], $provider['api_key']);
        $services = $api->getServices();

        if ($services !== null) {
            $this->json(['success' => true, 'services' => $services]);
        } else {
            $this->json(['error' => 'Could not fetch services.'], 500);
        }
    }
}
