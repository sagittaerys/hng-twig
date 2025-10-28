# TicketApp - Twig Implementation

A robust ticket management web application built with PHP and Twig templating engine. This is part of a multi-framework project that includes identical implementations in React, Vue.js, and Twig.

##  Features

- **Landing Page**: Welcoming hero section with wavy SVG background and decorative circles
- **Authentication**: Secure login and signup with form validation
- **Dashboard**: Real-time statistics for total, open, in-progress, and closed tickets
- **Ticket Management**: Full CRUD operations (Create, Read, Update, Delete)
- **Form Validation**: Client-side and server-side validation with clear error messages
- **Toast Notifications**: User-friendly feedback for all actions
- **Responsive Design**: Mobile-first approach with tablet and desktop layouts
- **Session Management**: Secure authentication using `ticketapp_session` in localStorage
- **Protected Routes**: Unauthorized access redirects to login page

## 🛠 Tech Stack

- **Backend**: PHP 7.4+
- **Templating Engine**: Twig 3.0
- **Frontend**: Vanilla JavaScript
- **Styling**: Custom CSS (shared with React and Vue versions)

## 📁 Project Structure

```
hng-twig/
├── public/
│   ├── index.php              # Main entry point & routing
│   ├── css/
│   │   └── styles.css         # Shared CSS styles
│   └── js/
│       └── app.js             # Client-side JavaScript
├── templates/
│   ├── layout.twig            # Base layout template
│   ├── landing.twig           # Landing page
│   ├── login.twig             # Login page
│   ├── signup.twig            # Signup page
│   ├── dashboard.twig         # Dashboard with statistics
│   └── tickets.twig           # Ticket management page
├── src/
│   ├── Database.php           # JSON-based database handler
│   ├── Auth.php               # Authentication logic
│   └── Ticket.php             # Ticket CRUD operations
├── vendor/                    # Composer dependencies
├── data.json                  # Database file (auto-generated)
├── composer.json              # PHP dependencies
└── README.md                  # Documentation
```

## 🔧 Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- Composer (PHP package manager)

### Step 1: Clone the Repository

```bash
git clone <your-repository-url>
cd hng-twig
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Copy CSS