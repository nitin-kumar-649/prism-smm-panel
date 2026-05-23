<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Models\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect($this->isAdmin() ? '/admin' : '/user');
        }
        $this->view('auth.login', ['title' => 'Login']);
    }

    public function login(): void
    {
        if (!$this->validateCsrf()) return;

        $email = $this->input('email');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->json(['error' => 'Please fill in all fields.'], 400);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $this->json(['error' => 'Invalid credentials.'], 401);
            return;
        }

        if ($user['status'] === 'banned') {
            $this->json(['error' => 'Your account has been suspended.'], 403);
            return;
        }

        if ($this->userModel->isLocked($user['id'])) {
            $this->json(['error' => 'Account temporarily locked. Try again in 15 minutes.'], 429);
            return;
        }

        if (!password_verify($password, $user['password'])) {
            $this->userModel->incrementLoginAttempts($user['id']);
            $this->json(['error' => 'Invalid credentials.'], 401);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['username'] = $user['username'];

        $this->userModel->recordLogin($user['id'], $_SERVER['REMOTE_ADDR'] ?? '');
        Logger::info("User logged in", ['user_id' => $user['id'], 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);

        $redirect = $user['role'] === 'admin' ? '/admin' : '/user';
        $this->json(['success' => true, 'redirect' => $redirect]);
    }

    public function showRegister(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/user');
        }
        $this->view('auth.register', ['title' => 'Create Account']);
    }

    public function register(): void
    {
        if (!$this->validateCsrf()) return;

        $username = $this->input('username');
        $email = $this->input('email');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];
        if (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Username must be 3-50 characters.';
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }
        if (!is_valid_email($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            $this->json(['error' => implode(' ', $errors)], 400);
            return;
        }

        if ($this->userModel->findByEmail($email)) {
            $this->json(['error' => 'Email already registered.'], 400);
            return;
        }
        if ($this->userModel->findByUsername($username)) {
            $this->json(['error' => 'Username already taken.'], 400);
            return;
        }

        $userId = $this->userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'role' => 'user',
            'status' => 'active',
            'api_key' => generate_api_key(),
        ]);

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = 'user';
        $_SESSION['username'] = $username;

        Logger::info("New user registered", ['user_id' => $userId]);
        $this->json(['success' => true, 'redirect' => '/user']);
    }

    public function showForgotPassword(): void
    {
        $this->view('auth.forgot-password', ['title' => 'Forgot Password']);
    }

    public function sendOtp(): void
    {
        if (!$this->validateCsrf()) return;

        $email = $this->input('email');
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $this->json(['error' => 'No account found with that email.'], 404);
            return;
        }

        $otpLength = (int) env('OTP_LENGTH', 6);
        $otp = str_pad((string) random_int(0, (int) pow(10, $otpLength) - 1), $otpLength, '0', STR_PAD_LEFT);
        $this->userModel->setOtp($user['id'], $otp);

        // In production, send via email. For now, log it.
        Logger::info("OTP generated for password reset", ['user_id' => $user['id'], 'otp' => $otp]);

        $this->json([
            'success' => true,
            'message' => 'OTP sent to your email.',
            'user_id' => $user['id'],
            'debug_otp' => env('APP_DEBUG') ? $otp : null
        ]);
    }

    public function verifyOtp(): void
    {
        if (!$this->validateCsrf()) return;

        $userId = (int) $this->input('user_id');
        $otp = $this->input('otp');

        if ($this->userModel->verifyOtp($userId, $otp)) {
            $_SESSION['reset_user_id'] = $userId;
            $this->json(['success' => true, 'message' => 'OTP verified.']);
        } else {
            $this->json(['error' => 'Invalid or expired OTP.'], 400);
        }
    }

    public function resetPassword(): void
    {
        if (!$this->validateCsrf()) return;

        $userId = $_SESSION['reset_user_id'] ?? 0;
        if (!$userId) {
            $this->json(['error' => 'Invalid reset session.'], 400);
            return;
        }

        $password = $_POST['password'] ?? '';
        if (strlen($password) < 8) {
            $this->json(['error' => 'Password must be at least 8 characters.'], 400);
            return;
        }

        $this->userModel->update($userId, [
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'otp_code' => null,
            'otp_expires' => null
        ]);

        unset($_SESSION['reset_user_id']);
        Logger::info("Password reset", ['user_id' => $userId]);
        $this->json(['success' => true, 'message' => 'Password updated. Please login.', 'redirect' => '/login']);
    }

    public function logout(): void
    {
        Logger::info("User logged out", ['user_id' => $_SESSION['user_id'] ?? 0]);
        session_destroy();
        header('Location: /login');
        exit;
    }
}
