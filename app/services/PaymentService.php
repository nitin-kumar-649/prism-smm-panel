<?php
/**
 * Payment Service
 * Handles payment processing for multiple gateways
 */

namespace App\Services;

use App\Core\Logger;
use App\Core\Database;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Notification;

class PaymentService
{
    private Database $db;
    private Transaction $transaction;
    private User $userModel;
    private Notification $notification;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->transaction = new Transaction();
        $this->userModel = new User();
        $this->notification = new Notification();
    }

    public function createPaypalPayment(int $userId, float $amount): array
    {
        $config = require BASE_PATH . '/config/payment.php';
        $paypal = $config['gateways']['paypal'];

        $paymentId = 'PP-' . strtoupper(bin2hex(random_bytes(8)));
        $this->db->insert('payment_requests', [
            'user_id' => $userId,
            'amount' => $amount,
            'gateway' => 'paypal',
            'gateway_payment_id' => $paymentId,
            'status' => 'pending'
        ]);

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'gateway' => 'paypal',
            'message' => 'PayPal payment initiated. Complete payment via PayPal.'
        ];
    }

    public function createStripePayment(int $userId, float $amount): array
    {
        $paymentId = 'ST-' . strtoupper(bin2hex(random_bytes(8)));
        $this->db->insert('payment_requests', [
            'user_id' => $userId,
            'amount' => $amount,
            'gateway' => 'stripe',
            'gateway_payment_id' => $paymentId,
            'status' => 'pending'
        ]);

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'gateway' => 'stripe',
            'message' => 'Stripe payment session created.'
        ];
    }

    public function createManualPayment(int $userId, float $amount, string $proof = ''): array
    {
        $paymentId = 'MN-' . strtoupper(bin2hex(random_bytes(8)));
        $this->db->insert('payment_requests', [
            'user_id' => $userId,
            'amount' => $amount,
            'gateway' => 'manual',
            'gateway_payment_id' => $paymentId,
            'status' => 'pending',
            'proof' => $proof
        ]);

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'gateway' => 'manual',
            'message' => 'Manual payment request submitted. Awaiting admin approval.'
        ];
    }

    public function completePayment(int $paymentRequestId): bool
    {
        $request = $this->db->fetch(
            "SELECT * FROM payment_requests WHERE id = ? AND status = 'pending'",
            [$paymentRequestId]
        );

        if (!$request) return false;

        $this->db->beginTransaction();
        try {
            $this->db->update('payment_requests', ['status' => 'completed'], 'id = ?', [$paymentRequestId]);
            $this->userModel->updateBalance($request['user_id'], $request['amount']);
            $this->transaction->createTransaction(
                $request['user_id'],
                'deposit',
                $request['amount'],
                "Deposit via {$request['gateway']}",
                $request['gateway'],
                $request['gateway_payment_id']
            );
            $this->notification->send(
                $request['user_id'],
                'Deposit Successful',
                format_money($request['amount']) . ' has been added to your wallet.',
                'success',
                '/user/wallet'
            );
            $this->db->commit();
            Logger::info("Payment completed", ['request_id' => $paymentRequestId, 'user_id' => $request['user_id'], 'amount' => $request['amount']]);
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error("Payment completion failed: " . $e->getMessage());
            return false;
        }
    }

    public function rejectPayment(int $paymentRequestId, string $reason = ''): bool
    {
        $request = $this->db->fetch(
            "SELECT * FROM payment_requests WHERE id = ? AND status = 'pending'",
            [$paymentRequestId]
        );

        if (!$request) return false;

        $this->db->update('payment_requests', [
            'status' => 'cancelled',
            'admin_note' => $reason
        ], 'id = ?', [$paymentRequestId]);

        $this->notification->send(
            $request['user_id'],
            'Payment Rejected',
            'Your deposit of ' . format_money($request['amount']) . ' was rejected.' . ($reason ? " Reason: {$reason}" : ''),
            'error'
        );

        return true;
    }

    public function getAvailableGateways(): array
    {
        $config = require BASE_PATH . '/config/payment.php';
        $available = [];
        foreach ($config['gateways'] as $key => $gateway) {
            if ($gateway['enabled'] ?? false) {
                $available[$key] = $gateway;
            }
        }
        return $available;
    }
}
