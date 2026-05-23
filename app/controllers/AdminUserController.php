<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Notification;

class AdminUserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $search = sanitize_input($_GET['search'] ?? '');

        $where = "role = 'user'";
        $params = [];
        if ($search) {
            $where .= ' AND (username LIKE ? OR email LIKE ?)';
            $params = ["%{$search}%", "%{$search}%"];
        }

        $users = $this->userModel->paginate($page, 25, $where, $params);

        $this->view('admin.users', [
            'title' => 'Users',
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function show(string $id): void
    {
        $user = $this->userModel->find((int) $id);
        if (!$user) {
            $this->redirect('/admin/users');
            return;
        }

        $this->view('admin.user-detail', [
            'title' => "User: {$user['username']}",
            'user' => $user,
        ]);
    }

    public function updateStatus(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $status = $this->input('status');
        if (!in_array($status, ['active', 'inactive', 'banned'])) {
            $this->json(['error' => 'Invalid status.'], 400);
            return;
        }

        $this->userModel->update((int) $id, ['status' => $status]);
        $this->json(['success' => true, 'message' => "User status changed to {$status}."]);
    }

    public function addFunds(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $amount = (float) $this->input('amount');
        if ($amount == 0) {
            $this->json(['error' => 'Amount must be non-zero.'], 400);
            return;
        }

        $userId = (int) $id;
        $type = $amount > 0 ? 'admin_add' : 'admin_deduct';
        $description = $amount > 0 ? 'Funds added by admin' : 'Funds deducted by admin';

        $this->userModel->updateBalance($userId, $amount);

        $transactionModel = new Transaction();
        $transactionModel->createTransaction($userId, $type, $amount, $description);

        $notificationModel = new Notification();
        $notificationModel->send(
            $userId,
            $amount > 0 ? 'Funds Added' : 'Funds Deducted',
            format_money(abs($amount)) . ($amount > 0 ? ' added to' : ' deducted from') . ' your wallet by admin.',
            $amount > 0 ? 'success' : 'warning'
        );

        $this->json(['success' => true, 'message' => 'Balance updated.']);
    }
}
