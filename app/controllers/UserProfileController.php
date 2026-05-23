<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class UserProfileController extends Controller
{
    public function index(): void
    {
        $this->view('user.profile', ['title' => 'Profile Settings']);
    }

    public function update(): void
    {
        if (!$this->validateCsrf()) return;

        $userId = current_user_id();
        $userModel = new User();

        $data = [];
        $username = $this->input('username');
        $phone = $this->input('phone');

        if ($username) {
            $existing = $userModel->findByUsername($username);
            if ($existing && $existing['id'] !== $userId) {
                $this->json(['error' => 'Username already taken.'], 400);
                return;
            }
            $data['username'] = $username;
            $_SESSION['username'] = $username;
        }

        if ($phone !== null) {
            $data['phone'] = $phone;
        }

        if (!empty($data)) {
            $userModel->update($userId, $data);
        }

        $this->json(['success' => true, 'message' => 'Profile updated.']);
    }

    public function changePassword(): void
    {
        if (!$this->validateCsrf()) return;

        $userId = current_user_id();
        $userModel = new User();
        $user = $userModel->find($userId);

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if (!password_verify($currentPassword, $user['password'])) {
            $this->json(['error' => 'Current password is incorrect.'], 400);
            return;
        }

        if (strlen($newPassword) < 8) {
            $this->json(['error' => 'New password must be at least 8 characters.'], 400);
            return;
        }

        $userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12])
        ]);

        $this->json(['success' => true, 'message' => 'Password changed successfully.']);
    }
}
