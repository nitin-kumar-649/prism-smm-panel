<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Setting;

class AdminSettingsController extends Controller
{
    private Setting $settingModel;

    public function __construct()
    {
        parent::__construct();
        $this->settingModel = new Setting();
    }

    public function index(): void
    {
        $settings = $this->settingModel->getAll();

        $this->view('admin.settings', [
            'title' => 'Settings',
            'settings' => $settings,
        ]);
    }

    public function update(): void
    {
        if (!$this->validateCsrf()) return;

        $fields = $_POST;
        unset($fields['csrf_token']);

        foreach ($fields as $key => $value) {
            $this->settingModel->set(sanitize_input($key), sanitize_input($value));
        }

        $this->json(['success' => true, 'message' => 'Settings saved.']);
    }
}
