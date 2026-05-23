<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Transaction;
use App\Services\PaymentService;

class UserWalletController extends Controller
{
    private Transaction $transactionModel;
    private PaymentService $paymentService;

    public function __construct()
    {
        parent::__construct();
        $this->transactionModel = new Transaction();
        $this->paymentService = new PaymentService();
    }

    public function index(): void
    {
        $userId = current_user_id();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $transactions = $this->transactionModel->getUserTransactions($userId, $page);
        $gateways = $this->paymentService->getAvailableGateways();

        $this->view('user.wallet', [
            'title' => 'Wallet',
            'transactions' => $transactions,
            'gateways' => $gateways,
        ]);
    }

    public function deposit(): void
    {
        if (!$this->validateCsrf()) return;

        $amount = (float) $this->input('amount');
        $gateway = $this->input('gateway');

        if ($amount < 1) {
            $this->json(['error' => 'Minimum deposit is $1.00'], 400);
            return;
        }
        if ($amount > 10000) {
            $this->json(['error' => 'Maximum deposit is $10,000.00'], 400);
            return;
        }

        $userId = current_user_id();

        $result = match ($gateway) {
            'paypal' => $this->paymentService->createPaypalPayment($userId, $amount),
            'stripe' => $this->paymentService->createStripePayment($userId, $amount),
            'manual' => $this->paymentService->createManualPayment($userId, $amount),
            default => ['success' => false, 'error' => 'Invalid payment method.'],
        };

        $this->json($result, $result['success'] ? 200 : 400);
    }
}
