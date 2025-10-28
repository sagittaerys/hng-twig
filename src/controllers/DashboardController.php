<?php 

namespace App\Controllers;

class DashboardController extends BaseController
{
    private $ticketsFile;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->ticketsFile = __DIR__ . '/../../data/tickets.json';

        $dataDir = dirname($this->ticketsFile);
        if (!is_dir($dataDir)) mkdir($dataDir, 0777, true);
        if (!file_exists($this->ticketsFile)) file_put_contents($this->ticketsFile, json_encode([]));
    }

    public function index()
    {
        $session = $this->ensureAuthenticated();
        $userId = $session['userId'];

        $allTickets = json_decode(file_get_contents($this->ticketsFile), true) ?: [];
        
        // Filter tickets for the current user using 'userId' (matches TicketController)
        $userTickets = array_filter($allTickets, function($ticket) use ($userId) {
            return isset($ticket['userId']) && (string)$ticket['userId'] === (string)$userId;
        });

        $stats = [
            'total' => count($userTickets),
            'open' => count(array_filter($userTickets, fn($t) => ($t['status'] ?? '') === 'open')),
            'resolved' => count(array_filter($userTickets, fn($t) => ($t['status'] ?? '') === 'closed'))
        ];

        $this->render('dashboard.twig', [
            'stats' => $stats,
            'user' => $session
        ]);
    }
}