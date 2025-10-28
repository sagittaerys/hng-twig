<?php
namespace App\Controllers;

class TicketController
{
    private string $storagePath;

    public function __construct(private $twig)
    {
        // data directory one level up from src (project root /data)
        $this->storagePath = realpath(__DIR__ . '/../../data') . '/tickets.json';
        // ensure file exists
        if (!file_exists(dirname($this->storagePath))) {
            mkdir(dirname($this->storagePath), 0755, true);
        }
        if (!file_exists($this->storagePath)) {
            file_put_contents($this->storagePath, json_encode([]));
        }
    }

    private function loadTickets(): array
    {
        $json = @file_get_contents($this->storagePath);
        if ($json === false) return [];
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    private function saveTickets(array $tickets): bool
    {
        return file_put_contents($this->storagePath, json_encode(array_values($tickets), JSON_PRETTY_PRINT)) !== false;
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    private function getUser()
    {
        // expect session structure like:
        // $_SESSION['ticketapp_session'] = ['userId' => '...', 'name' => '...', 'expiresAt' => '...'];
        return $_SESSION['ticketapp_session'] ?? null;
    }

    private function ensureAuthenticated()
    {
        $session = $this->getUser();
        if (!$session) {
            $this->flash('error', 'You must be logged in to access that page.');
            header('Location: /login');
            exit;
        }
        // optional expiry check
        if (!empty($session['expiresAt']) && strtotime($session['expiresAt']) < time()) {
            unset($_SESSION['ticketapp_session']);
            $this->flash('error', 'Your session has expired — please log in again.');
            header('Location: /login');
            exit;
        }
        return $session;
    }

    public function index()
    {
        $session = $this->ensureAuthenticated();
        $userId = $session['userId'];

        $all = $this->loadTickets();
        // filter tickets for this user
        $tickets = array_values(array_filter($all, function($t) use ($userId) {
            return isset($t['userId']) && (string)$t['userId'] === (string)$userId;
        }));

        // stats
        $open = count(array_filter($tickets, fn($t) => ($t['status'] ?? '') === 'open'));
        $resolved = count(array_filter($tickets, fn($t) => ($t['status'] ?? '') === 'closed'));
        $total = count($tickets);

        // support modal open via query (e.g., ?modal=edit&id=123 or ?modal=create or ?delete=123)
        $showModal = false;
        $editingTicket = null;
        $showDeleteConfirm = false;
        $ticketToDelete = null;

        if (!empty($_GET['modal']) && $_GET['modal'] === 'create') {
            $showModal = true;
        }
        if (!empty($_GET['modal']) && $_GET['modal'] === 'edit' && !empty($_GET['id'])) {
            $id = $_GET['id'];
            foreach ($tickets as $t) {
                if ((string)$t['id'] === (string)$id) {
                    $editingTicket = $t;
                    $showModal = true;
                    break;
                }
            }
        }
        if (!empty($_GET['delete']) && !empty($_GET['id'])) {
            $id = $_GET['id'];
            foreach ($tickets as $t) {
                if ((string)$t['id'] === (string)$id) {
                    $ticketToDelete = $t;
                    $showDeleteConfirm = true;
                    break;
                }
            }
        }

        // read flash
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        echo $this->twig->render('tickets.twig', [
            'user' => $session,
            'tickets' => $tickets,
            'stats' => ['total' => $total, 'open' => $open, 'resolved' => $resolved],
            'editingTicket' => $editingTicket,
            'showModal' => $showModal,
            'showDeleteConfirm' => $showDeleteConfirm,
            'ticketToDelete' => $ticketToDelete,
            'flash' => $flash,
        ]);
    }

    public function create()
    {
        $session = $this->ensureAuthenticated();
        $userId = $session['userId'];

        // gather input
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? '';
        $priority = $_POST['priority'] ?? 'medium';

        $errors = [];

        if ($title === '') {
            $errors['title'] = 'Title is required.';
        }
        $allowed = ['open','in_progress','closed'];
        if ($status === '' || !in_array($status, $allowed, true)) {
            $errors['status'] = "Status must be one of: open, in_progress, closed.";
        }
        if ($description !== '' && mb_strlen($description) > 500) {
            $errors['description'] = 'Description must be less than 500 characters.';
        }

        if (!empty($errors)) {
            // preserve errors and old input via session and redirect back with modal open
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = ['title'=>$title, 'description'=>$description, 'status'=>$status, 'priority'=>$priority];
            header('Location: /tickets?modal=create');
            exit;
        }

        $all = $this->loadTickets();

        $newTicket = [
            'id' => (string) time() . rand(100,999),
            'userId' => (string)$userId,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'priority' => $priority,
            'createdAt' => date('c'),
            'updatedAt' => date('c'),
        ];

        $all[] = $newTicket;
        if ($this->saveTickets($all)) {
            $this->flash('success', 'Ticket created successfully!');
            header('Location: /tickets');
            exit;
        } else {
            $this->flash('error', 'Failed to save ticket. Please try again.');
            header('Location: /tickets?modal=create');
            exit;
        }
    }

    public function update()
    {
        $session = $this->ensureAuthenticated();
        $userId = $session['userId'];

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->flash('error', 'Missing ticket ID.');
            header('Location: /tickets');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? '';
        $priority = $_POST['priority'] ?? 'medium';

        $errors = [];

        if ($title === '') {
            $errors['title'] = 'Title is required.';
        }
        $allowed = ['open','in_progress','closed'];
        if ($status === '' || !in_array($status, $allowed, true)) {
            $errors['status'] = "Status must be one of: open, in_progress, closed.";
        }
        if ($description !== '' && mb_strlen($description) > 500) {
            $errors['description'] = 'Description must be less than 500 characters.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = ['title'=>$title, 'description'=>$description, 'status'=>$status, 'priority'=>$priority];
            header('Location: /tickets?modal=edit&id=' . urlencode($id));
            exit;
        }

        $all = $this->loadTickets();
        $found = false;
        foreach ($all as &$t) {
            if ((string)$t['id'] === (string)$id && (string)$t['userId'] === (string)$userId) {
                $t['title'] = $title;
                $t['description'] = $description;
                $t['status'] = $status;
                $t['priority'] = $priority;
                $t['updatedAt'] = date('c');
                $found = true;
                break;
            }
        }
        unset($t);

        if (!$found) {
            $this->flash('error', 'Ticket not found or you are not authorized.');
            header('Location: /tickets');
            exit;
        }

        if ($this->saveTickets($all)) {
            $this->flash('success', 'Ticket updated successfully!');
            header('Location: /tickets');
            exit;
        } else {
            $this->flash('error', 'Failed to update ticket. Please try again.');
            header('Location: /tickets?modal=edit&id=' . urlencode($id));
            exit;
        }
    }

    public function delete()
    {
        $session = $this->ensureAuthenticated();
        $userId = $session['userId'];

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->flash('error', 'Missing ticket ID.');
            header('Location: /tickets');
            exit;
        }

        $all = $this->loadTickets();
        $new = array_values(array_filter($all, function($t) use ($id, $userId) {
            // remove only if id matches and userId matches
            return !( (string)$t['id'] === (string)$id && (string)$t['userId'] === (string)$userId );
        }));

        if (count($all) === count($new)) {
            $this->flash('error', 'Ticket not found or you are not authorized.');
            header('Location: /tickets');
            exit;
        }

        if ($this->saveTickets($new)) {
            $this->flash('success', 'Ticket deleted successfully!');
            header('Location: /tickets');
            exit;
        } else {
            $this->flash('error', 'Failed to delete ticket. Please try again.');
            header('Location: /tickets');
            exit;
        }
    }
}
