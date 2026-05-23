<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Service;
use App\Models\Category;

class HomeController extends Controller
{
    public function index(): void
    {
        $serviceModel = new Service();
        $categoryModel = new Category();

        $categories = $categoryModel->getActive();
        $services = $serviceModel->getGroupedByCategory();

        $this->view('auth.home', [
            'title' => env('APP_NAME', 'Prism SMM Panel') . ' - Premium Social Media Marketing',
            'categories' => $categories,
            'services' => $services,
        ]);
    }

    public function page(string $slug): void
    {
        $page = $this->db->fetch("SELECT * FROM pages WHERE slug = ? AND status = 'active'", [$slug]);
        if (!$page) {
            http_response_code(404);
            include BASE_PATH . '/templates/errors/404.php';
            return;
        }

        $this->view('auth.page', [
            'title' => $page['meta_title'] ?: $page['title'],
            'page' => $page
        ]);
    }
}
