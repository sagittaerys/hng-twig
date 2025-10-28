# Ticket Management System

A lightweight, PHP-based ticket management web application built with Twig templating engine. This application allows users to create, manage, and track support tickets with an intuitive interface.

## 🌟 Features

- **User Authentication**: Secure signup and login system with session management
- **Ticket Management**: Create, update, view, and delete support tickets
- **Dashboard**: Overview of ticket statistics (total, open, resolved)
- **Status Tracking**: Track tickets through different states (Open, In Progress, Closed)
- **Priority Levels**: Set ticket priorities (Low, Medium, High)
- **Responsive Design**: Mobile-friendly interface
- **File-based Storage**: Uses JSON files for data persistence (no database required)

## 📋 Requirements

- PHP 8.1 or higher
- Composer
- Git

## 🚀 Installation

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/sagittaerys/hng-twig.git
   cd hng-twig
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Create data directory** (if it doesn't exist)
   ```bash
   mkdir -p data
   chmod 777 data
   ```

4. **Start PHP development server**
   ```bash
   php -S localhost:8000 -t public
   ```

5. **Access the application**
   
   Open your browser and navigate to: `http://localhost:8000`

## 📁 Project Structure

```
hng-twig/
├── public/                 # Public directory (web root)
│   ├── index.php          # Application entry point
│   ├── .htaccess          # Apache rewrite rules
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── assets/            # Images and other assets
├── src/                   # Application source code
│   └── controllers/       # Controller classes
│       ├── BaseController.php
│       ├── AuthController.php
│       ├── DashboardController.php
│       └── TicketController.php
├── templates/             # Twig template files
│   ├── base.twig
│   ├── layout.twig
│   ├── landing.twig
│   ├── login.twig
│   ├── signup.twig
│   ├── dashboard.twig
│   ├── tickets.twig
│   ├── navbar.twig
│   └── footer.twig
├── data/                  # Data storage (JSON files)
│   ├── users.json        # User accounts
│   └── tickets.json      # Ticket data
├── vendor/                # Composer dependencies
├── composer.json          # Composer configuration
├── Dockerfile            # Docker configuration
└── README.md             # This file
```

## 🎯 Usage

### Creating an Account

1. Navigate to the homepage
2. Click "Sign Up" or "Get Started"
3. Fill in your details (name, email, password)
4. Click "Sign Up"

### Logging In

1. Click "Login" from the homepage
2. Enter your email and password
3. Click "Login"

### Managing Tickets

#### Creating a Ticket
1. After logging in, go to the Dashboard or Tickets page
2. Click "Create New Ticket" button
3. Fill in:
   - **Title**: Brief description of the issue
   - **Description**: Detailed explanation (max 500 characters)
   - **Status**: Open, In Progress, or Closed
   - **Priority**: Low, Medium, or High
4. Click "Create Ticket"

#### Updating a Ticket
1. From the Tickets page, click the edit icon (pencil) on any ticket
2. Modify the fields you want to update
3. Click "Update Ticket"

#### Deleting a Ticket
1. From the Tickets page, click the delete icon (trash) on any ticket
2. Confirm the deletion

## 🔧 Configuration

### Session Settings

Sessions are configured in `public/index.php`:
```php
session_start();
```

Session timeout is set in `AuthController.php`:
```php
$_SESSION['expires_at'] = time() + (30 * 60); // 30 minutes
```

### Data Storage

The application uses JSON files for storage located in the `data/` directory:
- `users.json`: Stores user account information
- `tickets.json`: Stores all ticket data

**Important**: Ensure the `data/` directory has write permissions:
```bash
chmod 777 data
```

## 🌐 Deployment

### Deploy to Render

1. **Push your code to GitHub**

2. **Create Render account** at [render.com](https://render.com)

3. **Create new Web Service**
   - Connect your GitHub repository
   - Select `hng-twig` repository
   - Environment: Docker
   - Render will auto-detect the Dockerfile

4. **Deploy**
   - Click "Create Web Service"
   - Wait for deployment to complete
   - Access your app at the provided URL

### Deploy to Railway

1. **Push your code to GitHub**

2. **Create Railway account** at [railway.app](https://railway.app)

3. **Create new project**
   - Select "Deploy from GitHub repo"
   - Choose `hng-twig` repository
   - Railway will auto-detect and deploy

### Deploy to Heroku

1. **Create `Procfile`** in project root:
   ```
   web: php -S 0.0.0.0:$PORT -t public
   ```

2. **Deploy**:
   ```bash
   heroku create your-app-name
   git push heroku main
   ```

### Environment Variables

No environment variables are required for basic operation. However, you can add:

- `SESSION_SECRET`: For enhanced session security
- `PHP_MEMORY_LIMIT`: To adjust PHP memory limits

## 🔒 Security Features

- **Password Hashing**: Uses PHP's `password_hash()` with bcrypt
- **Session Management**: Automatic session expiration after 30 minutes
- **CSRF Protection**: Session-based authentication
- **User Isolation**: Users can only view/edit their own tickets
- **Input Validation**: All user inputs are validated and sanitized

## 🐛 Troubleshooting

### "Class not found" errors
```bash
composer dump-autoload
```

### Permission denied on data directory
```bash
chmod 777 data
```

### Session errors
Ensure session is started before any output in `index.php`

### Template not found
Verify that the `templates/` directory exists and contains all required `.twig` files

## 🛠️ Development

### Adding New Routes

Edit `public/index.php`:
```php
case '/your-route':
    // Your route logic here
    break;
```

### Creating New Controllers

1. Create file in `src/controllers/`
2. Extend `BaseController`
3. Use namespace `App\Controllers`

Example:
```php
<?php
namespace App\Controllers;

class YourController extends BaseController
{
    public function index()
    {
        $this->render('your-template.twig', [
            'data' => 'value'
        ]);
    }
}
```

### Adding New Templates

Create `.twig` files in the `templates/` directory. Use Twig syntax:
```twig
{% extends "layout.twig" %}

{% block content %}
    <h1>Your Content</h1>
{% endblock %}
```

## 📦 Dependencies

- **twig/twig** (^3.0): Templating engine
- **vlucas/phpdotenv** (^5.5): Environment variable management

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 👥 Authors

- **Sagittaerys** - [GitHub Profile](https://github.com/sagittaerys)

## 🙏 Acknowledgments

- Built as part of HNG Internship
- Powered by Twig templating engine
- Inspired by modern ticket management systems

## 📞 Support

For support, issues, or questions:
- Open an issue on [GitHub](https://github.com/sagittaerys/hng-twig/issues)
- Contact: [Your Email]

## 🔗 Links

- **Live Demo**: [Your Render URL]
- **GitHub Repository**: https://github.com/sagittaerys/hng-twig.git
- **HNG Internship**: [Learn more about HNG](https://hng.tech)

---

**Made with ❤️ for HNG Internship**