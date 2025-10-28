<?php
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Enable ALL error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// IMPORTANT: Start session BEFORE any output
session_start();

try {
    // Check if vendor exists
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        die('ERROR: Composer dependencies not installed. Run: composer install');
    }
    
    require_once __DIR__ . '/../vendor/autoload.php';

    // Check if templates directory exists
    if (!is_dir(__DIR__ . '/../templates')) {
        die('ERROR: templates/ directory not found');
    }

    // Initialize Twig
    $loader = new FilesystemLoader(__DIR__ . '/../templates');
    $twig = new Environment($loader, [
        'cache' => false,
        'debug' => true,
    ]);

    // Add global function to check authentication
    $twig->addGlobal('session', $_SESSION);

    // Add custom functions for status colors and labels
    $twig->addFunction(new \Twig\TwigFunction('getStatusColor', function($status) {
        switch ($status) {
            case 'open': return '#10b981';
            case 'in_progress': return '#f59e0b';
            case 'closed': return '#6b7280';
            default: return '#6b7280';
        }
    }));

    $twig->addFunction(new \Twig\TwigFunction('getStatusLabel', function($status) {
        switch ($status) {
            case 'open': return 'Open';
            case 'in_progress': return 'In Progress';
            case 'closed': return 'Closed';
            default: return $status;
        }
    }));

    // Simple router
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    $path = str_replace($script_name, '', $request_uri);
    $path = parse_url($path, PHP_URL_PATH);
    $path = rtrim($path, '/');
    if (empty($path)) {
        $path = '/';
    }

    // Route handling
    switch ($path) {
        case '/':
            echo $twig->render('landing.twig');
            break;
        
        case '/login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once __DIR__ . '/../src/controllers/AuthController.php';
                $controller = new App\Controllers\AuthController($twig);
                $controller->login();
            } else {
                echo $twig->render('login.twig', ['error' => '']);
            }
            break;
        
        case '/sign-up':
        case '/signup':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once __DIR__ . '/../src/controllers/AuthController.php';
                $controller = new App\Controllers\AuthController($twig);
                $controller->signup();
            } else {
                echo $twig->render('signup.twig', ['error' => '']);
            }
            break;
        
        case '/dashboard':
            require_once __DIR__ . '/../src/controllers/DashboardController.php';
            $controller = new App\Controllers\DashboardController($twig);
            $controller->index();
            break;
        
        case '/tickets':
            require_once __DIR__ . '/../src/controllers/TicketController.php';
            $controller = new App\Controllers\TicketController($twig);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->create();
            } else {
                $controller->index();
            }
            break;
        
        case '/tickets/update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once __DIR__ . '/../src/controllers/TicketController.php';
                $controller = new App\Controllers\TicketController($twig);
                $controller->update();
            }
            break;
        
        case '/tickets/delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require_once __DIR__ . '/../src/controllers/TicketController.php';
                $controller = new App\Controllers\TicketController($twig);
                $controller->delete();
            }
            break;
        
        case '/logout':
            require_once __DIR__ . '/../src/controllers/AuthController.php';
            $controller = new App\Controllers\AuthController($twig);
            $controller->logout();
            break;
        
        default:
            http_response_code(404);
            echo $twig->render('404.twig');
            break;
    }

} catch (Exception $e) {
    // Force output and clear any buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
    echo '<h1 style="color: red; font-family: Arial;">Error Occurred</h1>';
    echo '<div style="background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; margin: 20px 0;">';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
    echo '</div>';
    echo '<h3>Stack Trace:</h3>';
    echo '<pre style="background: #f4f4f4; padding: 15px; overflow: auto; border: 1px solid #ddd;">';
    echo htmlspecialchars($e->getTraceAsString());
    echo '</pre>';
    echo '</body></html>';
    die();
}