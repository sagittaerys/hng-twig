<?php

namespace App\Controllers;

class BaseController
{
    protected $twig;

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    protected function render($template, $data = [])
    {
        $isLoggedIn = isset($_SESSION['user_id']);
        $userName   = $_SESSION['user_name'] ?? null;

        echo $this->twig->render($template, array_merge($data, [
            'isLoggedIn' => $isLoggedIn,
            'userName'   => $userName
        ]));
    }

    protected function ensureAuthenticated()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->flash('error', 'You must be logged in to access that page.');
            header('Location: /login');
            exit;
        }

        if (isset($_SESSION['expires_at']) && $_SESSION['expires_at'] < time()) {
            session_destroy();
            $this->flash('error', 'Your session has expired — please log in again.');
            header('Location: /login');
            exit;
        }

        return [
            'userId' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email']
        ];
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}