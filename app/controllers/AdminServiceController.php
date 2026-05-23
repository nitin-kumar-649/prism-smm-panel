<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Service;
use App\Models\Category;
use App\Models\Provider;

class AdminServiceController extends Controller
{
    private Service $serviceModel;
    private Category $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->serviceModel = new Service();
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        $services = $this->serviceModel->getAllWithDetails();
        $categories = $this->categoryModel->getWithServiceCount();
        $providerModel = new Provider();
        $providers = $providerModel->getActive();

        $this->view('admin.services', [
            'title' => 'Services',
            'services' => $services,
            'categories' => $categories,
            'providers' => $providers,
        ]);
    }

    public function createService(): void
    {
        if (!$this->validateCsrf()) return;

        $data = [
            'category_id' => (int) $this->input('category_id'),
            'name' => $this->input('name'),
            'description' => $this->input('description'),
            'type' => $this->input('type', 'default'),
            'price_per_1000' => (float) $this->input('price_per_1000'),
            'min_quantity' => (int) $this->input('min_quantity', 100),
            'max_quantity' => (int) $this->input('max_quantity', 10000),
            'provider_id' => ((int) $this->input('provider_id')) ?: null,
            'provider_service_id' => $this->input('provider_service_id') ?: null,
            'drip_feed' => (int) ($this->input('drip_feed') === 'on' || $this->input('drip_feed') === '1'),
            'refill' => (int) ($this->input('refill') === 'on' || $this->input('refill') === '1'),
            'cancel' => (int) ($this->input('cancel') === 'on' || $this->input('cancel') === '1'),
            'status' => $this->input('status', 'active'),
            'sort_order' => (int) $this->input('sort_order', 0),
        ];

        if (empty($data['name']) || $data['price_per_1000'] <= 0) {
            $this->json(['error' => 'Name and price are required.'], 400);
            return;
        }

        $this->serviceModel->create($data);
        $this->json(['success' => true, 'message' => 'Service created.']);
    }

    public function updateService(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $data = [
            'category_id' => (int) $this->input('category_id'),
            'name' => $this->input('name'),
            'description' => $this->input('description'),
            'type' => $this->input('type', 'default'),
            'price_per_1000' => (float) $this->input('price_per_1000'),
            'min_quantity' => (int) $this->input('min_quantity'),
            'max_quantity' => (int) $this->input('max_quantity'),
            'provider_id' => ((int) $this->input('provider_id')) ?: null,
            'provider_service_id' => $this->input('provider_service_id') ?: null,
            'drip_feed' => (int) ($this->input('drip_feed') === 'on' || $this->input('drip_feed') === '1'),
            'refill' => (int) ($this->input('refill') === 'on' || $this->input('refill') === '1'),
            'cancel' => (int) ($this->input('cancel') === 'on' || $this->input('cancel') === '1'),
            'status' => $this->input('status', 'active'),
            'sort_order' => (int) $this->input('sort_order', 0),
        ];

        $this->serviceModel->update((int) $id, $data);
        $this->json(['success' => true, 'message' => 'Service updated.']);
    }

    public function deleteService(string $id): void
    {
        $this->serviceModel->delete((int) $id);
        $this->json(['success' => true, 'message' => 'Service deleted.']);
    }

    public function createCategory(): void
    {
        if (!$this->validateCsrf()) return;

        $name = $this->input('name');
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));

        $this->categoryModel->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $this->input('description'),
            'sort_order' => (int) $this->input('sort_order', 0),
            'status' => $this->input('status', 'active'),
        ]);

        $this->json(['success' => true, 'message' => 'Category created.']);
    }

    public function deleteCategory(string $id): void
    {
        $this->categoryModel->delete((int) $id);
        $this->json(['success' => true, 'message' => 'Category deleted.']);
    }
}
