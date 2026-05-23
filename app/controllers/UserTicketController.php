<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ticket;

class UserTicketController extends Controller
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
        $tickets = $this->ticketModel->getUserTickets(current_user_id(), $page);

        $this->view('user.tickets', [
            'title' => 'Support Tickets',
            'tickets' => $tickets,
        ]);
    }

    public function create(): void
    {
        if (!$this->validateCsrf()) return;

        $subject = $this->input('subject');
        $message = $this->input('message');
        $priority = $this->input('priority', 'medium');

        if (empty($subject) || empty($message)) {
            $this->json(['error' => 'Subject and message are required.'], 400);
            return;
        }

        $ticketId = 'TKT-' . strtoupper(bin2hex(random_bytes(4)));
        $id = $this->ticketModel->create([
            'ticket_id' => $ticketId,
            'user_id' => current_user_id(),
            'subject' => $subject,
            'priority' => $priority,
            'status' => 'open'
        ]);

        $this->ticketModel->addMessage($id, current_user_id(), $message);

        $this->json(['success' => true, 'message' => 'Ticket created successfully.', 'ticket_id' => $ticketId]);
    }

    public function show(string $id): void
    {
        $ticket = $this->ticketModel->find((int) $id);
        if (!$ticket || $ticket['user_id'] !== current_user_id()) {
            $this->redirect('/user/tickets');
            return;
        }

        $messages = $this->ticketModel->getMessages((int) $id);

        $this->view('user.ticket-detail', [
            'title' => "Ticket #{$ticket['ticket_id']}",
            'ticket' => $ticket,
            'messages' => $messages,
        ]);
    }

    public function reply(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $ticket = $this->ticketModel->find((int) $id);
        if (!$ticket || $ticket['user_id'] !== current_user_id()) {
            $this->json(['error' => 'Ticket not found.'], 404);
            return;
        }

        $message = $this->input('message');
        if (empty($message)) {
            $this->json(['error' => 'Message is required.'], 400);
            return;
        }

        $this->ticketModel->addMessage((int) $id, current_user_id(), $message);
        $this->json(['success' => true, 'message' => 'Reply sent.']);
    }

    public function close(string $id): void
    {
        $ticket = $this->ticketModel->find((int) $id);
        if (!$ticket || $ticket['user_id'] !== current_user_id()) {
            $this->json(['error' => 'Ticket not found.'], 404);
            return;
        }

        $this->ticketModel->update((int) $id, ['status' => 'closed']);
        $this->json(['success' => true, 'message' => 'Ticket closed.']);
    }
}
