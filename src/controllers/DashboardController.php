<?php

namespace App\Controllers;

class DashboardController
{
    private $twig;
    private $ticketsFile;

    public function __construct($twig)
    {
        $this->twig = $twig;
        $this->ticketsFile = __DIR__ . '/../../data/tickets.json';
        
        // Create data directory if it doesn't exist
        $dataDir = dirname($this->ticketsFile);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }
        
        // Initialize tickets file if it doesn't exist
        if (!file_exists($this->ticketsFile)) {
            file_put_contents($this->ticketsFile, json_encode([]));
        }
    }

    public function index()
    {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Check session expiration
        if (isset($_SESSION['expires_at']) && $_SESSION['expires_at'] < time()) {
            session_destroy();
            header('Location: /login');
            exit;
        }

        // Load tickets
        $allTickets = json_decode(file_get_contents($this->ticketsFile), true);
        $userTickets = array_filter($allTickets, function($ticket) {
            return $ticket['user_id'] === $_SESSION['user_id'];
        });

        // Calculate stats
        $stats = [
            'total' => count($userTickets),
            'open' => count(array_filter($userTickets, function($t) {
                return $t['status'] === 'open';
            })),
            'resolved' => count(array_filter($userTickets, function($t) {
                return $t['status'] === 'closed';
            }))
        ];

        echo $this->twig->render('dashboard.twig', [
            'stats' => $stats
        ]);
    }
}