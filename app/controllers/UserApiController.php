<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserApiController extends Controller
{
    public function index(): void
    {
        $this->view('user.api', ['title' => 'API Documentation']);
    }

    public function regenerateKey(): void
    {
        if (!$this->validateCsrf()) return;

        $userModel = new User();
        $newKey = generate_api_key();
        $userModel->update(current_user_id(), ['api_key' => $newKey]);

        $this->json(['success' => true, 'api_key' => $newKey]);
    }
}
