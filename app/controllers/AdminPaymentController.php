<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PaymentService;

class AdminPaymentController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $status = sanitize_input($_GET['status'] ?? '');

        $where = '1=1';
        $params = [];
        if ($status) {
            $where .= ' AND pr.status = ?';
            $params[] = $status;
        }

        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        $total = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM payment_requests pr WHERE {$where}",
            $params
        )['cnt'];

        $payments = $this->db->fetchAll(
            "SELECT pr.*, u.username, u.email
             FROM payment_requests pr
             JOIN users u ON pr.user_id = u.id
             WHERE {$where}
             ORDER BY pr.id DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->view('admin.payments', [
            'title' => 'Payment Requests',
            'payments' => [
                'data' => $payments,
                'current_page' => $page,
                'total_pages' => (int) ceil($total / $perPage),
                'total' => (int) $total,
            ],
            'status_filter' => $status,
        ]);
    }

    public function approve(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $paymentService = new PaymentService();
        $result = $paymentService->completePayment((int) $id);

        $this->json($result
            ? ['success' => true, 'message' => 'Payment approved.']
            : ['error' => 'Failed to approve payment.'], $result ? 200 : 400
        );
    }

    public function reject(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $reason = $this->input('reason', '');
        $paymentService = new PaymentService();
        $result = $paymentService->rejectPayment((int) $id, $reason);

        $this->json($result
            ? ['success' => true, 'message' => 'Payment rejected.']
            : ['error' => 'Failed to reject payment.'], $result ? 200 : 400
        );
    }
}
