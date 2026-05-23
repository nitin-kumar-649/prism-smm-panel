<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ticket;
use App\Models\Notification;

class AdminTicketController extends Controller
{
    private Ticket $ticketModel;

    public function __construct()
    {
        parent::__construct();
        $this->ticketModel = new Ticket();
    }

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $status = sanitize_input($_GET['status'] ?? '');
        $tickets = $this->ticketModel->getWithUser($page, 25, $status);

        $this->view('admin.tickets', [
            'title' => 'Support Tickets',
            'tickets' => $tickets,
            'status_filter' => $status,
        ]);
    }

    public function show(string $id): void
    {
        $ticket = $this->ticketModel->find((int) $id);
        if (!$ticket) {
            $this->redirect('/admin/tickets');
            return;
        }

        $messages = $this->ticketModel->getMessages((int) $id);
        $user = $this->db->fetch("SELECT username, email FROM users WHERE id = ?", [$ticket['user_id']]);

        $this->view('admin.ticket-detail', [
            'title' => "Ticket #{$ticket['ticket_id']}",
            'ticket' => $ticket,
            'messages' => $messages,
            'ticketUser' => $user,
        ]);
    }

    public function reply(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $ticket = $this->ticketModel->find((int) $id);
        if (!$ticket) {
            $this->json(['error' => 'Ticket not found.'], 404);
            return;
        }

        $message = $this->input('message');
        if (empty($message)) {
            $this->json(['error' => 'Message is required.'], 400);
            return;
        }

        $this->ticketModel->addMessage((int) $id, current_user_id(), $message, true);

        $notificationModel = new Notification();
        $notificationModel->send(
            $ticket['user_id'],
            'Ticket Reply',
            "Your ticket #{$ticket['ticket_id']} has been answered.",
            'info',
            "/user/tickets/{$id}"
        );

        $this->json(['success' => true, 'message' => 'Reply sent.']);
    }

    public function close(string $id): void
    {
        $this->ticketModel->update((int) $id, ['status' => 'closed']);
        $this->json(['success' => true, 'message' => 'Ticket closed.']);
    }
}
