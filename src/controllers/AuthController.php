<?php

namespace App\Controllers;

class AuthController
{
    private $twig;
    private $usersFile;

    public function __construct($twig)
    {
        $this->twig = $twig;
        $this->usersFile = __DIR__ . '/../../data/users.json';
        
        // Create data directory if it doesn't exist
        $dataDir = dirname($this->usersFile);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }
        
        // Initialize users file if it doesn't exist
        if (!file_exists($this->usersFile)) {
            file_put_contents($this->usersFile, json_encode([]));
        }
    }

    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $errors = [];
        
        // Validation
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }
        
        if (!empty($errors)) {
            echo $this->twig->render('login.twig', [
                'errors' => $errors,
                'email' => $email,
                'error' => 'Please fix the errors below'
            ]);
            return;
        }
        
        // Check credentials
        $users = json_decode(file_get_contents($this->usersFile), true);
        $user = null;
        
        foreach ($users as $u) {
            if ($u['email'] === $email) {
                $user = $u;
                break;
            }
        }
        
        if (!$user || !password_verify($password, $user['password'])) {
            echo $this->twig->render('login.twig', [
                'error' => 'Invalid email or password',
                'email' => $email
            ]);
            return;
        }
        
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['expires_at'] = time() + (24 * 60 * 60); // 24 hours
        
        header('Location: /dashboard');
        exit;
    }

    public function signup()
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($name) < 2) {
            $errors['name'] = 'Name must be at least 2 characters';
        }
        
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters';
        }
        
        if (empty($confirmPassword)) {
            $errors['confirmPassword'] = 'Please confirm your password';
        } elseif ($password !== $confirmPassword) {
            $errors['confirmPassword'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            echo $this->twig->render('signup.twig', [
                'errors' => $errors,
                'name' => $name,
                'email' => $email,
                'error' => 'Please fix the errors below'
            ]);
            return;
        }
        
        // Check if user already exists
        $users = json_decode(file_get_contents($this->usersFile), true);
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                echo $this->twig->render('signup.twig', [
                    'error' => 'Email already registered',
                    'name' => $name,
                    'email' => $email
                ]);
                return;
            }
        }
        
        // Create new user
        $newUser = [
            'id' => uniqid(),
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $users[] = $newUser;
        file_put_contents($this->usersFile, json_encode($users, JSON_PRETTY_PRINT));
        
        // Create session
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