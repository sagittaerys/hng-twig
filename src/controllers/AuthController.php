<?php

namespace App\Controllers;

class AuthController extends BaseController
{
    private $usersFile;

    public function __construct($twig)
    {
        parent::__construct($twig);
        $this->usersFile = __DIR__ . '/../../data/users.json';
        
        $dataDir = dirname($this->usersFile);
        if (!is_dir($dataDir)) mkdir($dataDir, 0777, true);
        if (!file_exists($this->usersFile)) file_put_contents($this->usersFile, json_encode([]));
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->render('login.twig');
            return;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $errors = [];

        if (empty($email)) $errors['email'] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
        if (empty($password)) $errors['password'] = 'Password is required';

        if ($errors) {
            $this->render('login.twig', ['errors' => $errors, 'email' => $email]);
            return;
        }

        $users = json_decode(file_get_contents($this->usersFile), true);
        $user = array_filter($users, fn($u) => $u['email'] === $email);
        $user = reset($user);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->render('login.twig', ['error' => 'Invalid email or password', 'email' => $email]);
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['expires_at'] = time() + (24 * 60 * 60);

        header('Location: /dashboard');
        exit;
    }

    public function signup()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->render('signup.twig');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $errors = [];

        if (empty($name)) $errors['name'] = 'Name is required';
        elseif (strlen($name) < 2) $errors['name'] = 'Name must be at least 2 characters';
        if (empty($email)) $errors['email'] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
        if (empty($password)) $errors['password'] = 'Password is required';
        elseif (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters';
        if ($password !== $confirmPassword) $errors['confirmPassword'] = 'Passwords do not match';

        if ($errors) {
            $this->render('signup.twig', ['errors' => $errors, 'name' => $name, 'email' => $email]);
            return;
        }

        $users = json_decode(file_get_contents($this->usersFile), true);
        if (array_filter($users, fn($u) => $u['email'] === $email)) {
            $this->render('signup.twig', ['error' => 'Email already registered']);
            return;
        }

        $newUser = [
            'id' => uniqid(),
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $users[] = $newUser;
        file_put_contents($this->usersFile, json_encode($users, JSON_PRETTY_PRINT));

        $_SESSION['user_id'] = $newUser['id'];
        $_SESSION['user_name'] = $newUser['name'];
        $_SESSION['user_email'] = $newUser['email'];
        $_SESSION['expires_at'] = time() + (24 * 60 * 60);

        header('Location: /dashboard');
        exit;
    }

    public function logout()
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}
