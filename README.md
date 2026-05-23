# Prism SMM Panel

A complete, production-level Social Media Marketing (SMM) panel built with PHP, MySQL, Tailwind CSS, and JavaScript. Features glassmorphism UI, MVC architecture, wallet system, payment gateway integration, provider API integration, and comprehensive admin/user panels.

## Features

### Core
- **MVC Architecture** – Clean separation of concerns with controllers, models, services, middleware
- **AJAX-Based SPA** – Fast, seamless page interactions without full reloads
- **Responsive Design** – Desktop, tablet, and mobile optimized with Tailwind CSS
- **Glassmorphism UI** – Modern glass-morphism design with dark/light theme support
- **Security** – CSRF protection, XSS prevention (htmlspecialchars), SQL injection prevention (PDO prepared statements), rate limiting, bcrypt password hashing

### User Panel
- Dashboard with order/spending statistics
- New order placement with service selection and live price calculation
- Bulk orders (multiple orders at once)
- Drip-feed support for gradual delivery
- Order tracking with refill/cancel requests
- Wallet with multiple payment gateways (PayPal, Stripe, Razorpay, Coinbase, Manual)
- Ticket support system
- API key management with documentation
- Profile settings and password change

### Admin Panel
- Analytics dashboard with revenue/order charts (Chart.js)
- Order management with status updates and auto-refund
- Service & category management (CRUD)
- User management with fund controls
- Payment request approval/rejection
- Provider API management with balance checking
- Ticket management with admin replies
- Site settings (SEO, registration, maintenance mode, announcements)

### API
- External REST API (`/api/v2`) for third-party integration
- Actions: services, add order, order status, multi-status, refill, cancel, balance check
- API key authentication

### Background Jobs
- `cron/order_sync.php` – Syncs order statuses from provider APIs
- `cron/refill_checker.php` – Processes refill requests
- `cron/payment_verify.php` – Verifies pending payments and auto-cancels expired ones

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` enabled
- XAMPP (recommended) or any LAMP/WAMP stack

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/nitin-kumar-649/prism-smm-panel.git
```

### 2. Configure XAMPP

Move or symlink the project to your XAMPP `htdocs` directory:

```bash
# Option A: Move
mv prism-smm-panel /path/to/xampp/htdocs/

# Option B: Symlink
ln -s /path/to/prism-smm-panel /path/to/xampp/htdocs/prism-smm-panel
```

### 3. Create Database

Open phpMyAdmin (`http://localhost/phpmyadmin`) and create a new database:

```sql
CREATE DATABASE prism_smm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Run Migrations

Import the SQL migration file:

```bash
mysql -u root prism_smm < database/migrations/001_create_tables.sql
```

Or paste the contents of `database/migrations/001_create_tables.sql` into phpMyAdmin's SQL tab.

### 5. Configure Environment

Copy `.env.example` to `.env` and update values:

```bash
cp .env.example .env
```

Key settings to update:
- `APP_URL` – Your application URL (e.g., `http://localhost/prism-smm-panel`)
- `APP_KEY` – Generate a random 32-character string
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` – Database credentials
- Payment gateway keys (optional, enable what you need)
- Mail settings for OTP (optional)

### 6. Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 public/uploads/
```

### 7. Apache Virtual Host (Optional)

For a clean URL, configure a virtual host:

```apache
<VirtualHost *:80>
    ServerName prism.local
    DocumentRoot "/path/to/prism-smm-panel"
    <Directory "/path/to/prism-smm-panel">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add `127.0.0.1 prism.local` to your hosts file.

### 8. Access the Panel

- **Frontend:** `http://localhost/prism-smm-panel/`
- **Admin Login:** `admin@prismsmm.com` / `admin123`

## Folder Structure

```
prism-smm-panel/
├── app/
│   ├── controllers/     # Request handlers
│   ├── core/            # Framework core (App, Router, Database, Model, Controller, Logger)
│   ├── helpers/         # Helper functions
│   ├── middleware/       # Auth, Admin, CSRF, RateLimit middleware
│   ├── models/          # Database models
│   ├── services/        # Business logic (OrderService, PaymentService, ProviderApiService)
│   └── validators/      # Input validators
├── api/
│   ├── providers/       # Provider API integrations
│   └── webhooks/        # Payment webhook handlers
├── config/              # App, database, payment configs
├── cron/                # Background job scripts
├── database/
│   ├── migrations/      # SQL schema files
│   └── seeds/           # Seed data (included in migration)
├── public/
│   ├── assets/
│   │   ├── css/         # Custom CSS (glassmorphism, animations)
│   │   └── js/          # Main JS (AJAX, toasts, notifications, theme)
│   └── uploads/         # User uploads
├── routes/
│   ├── web.php          # Web routes
│   └── api.php          # API routes
├── storage/
│   ├── cache/           # Rate limit cache
│   ├── logs/            # Application logs
│   └── sessions/        # PHP sessions
├── templates/
│   ├── admin/           # Admin panel views
│   ├── auth/            # Login, register, forgot password, home
│   ├── errors/          # Error pages
│   ├── layouts/         # Layout templates (app, auth, sidebar, navbar)
│   └── user/            # User panel views
├── .env                 # Environment configuration
├── .htaccess            # Apache rewrite rules & security headers
├── composer.json        # PHP dependencies
└── index.php            # Application entry point
```

## API Documentation

**Endpoint:** `POST /api/v2`

All requests require a `key` parameter (your API key).

### Get Services
```
key=YOUR_API_KEY&action=services
```

### Add Order
```
key=YOUR_API_KEY&action=add&service=1&link=https://instagram.com/user&quantity=1000
```

### Order Status
```
key=YOUR_API_KEY&action=status&order=12345
```

### Multi Order Status
```
key=YOUR_API_KEY&action=status&orders=1,2,3
```

### Refill
```
key=YOUR_API_KEY&action=refill&order=12345
```

### Cancel
```
key=YOUR_API_KEY&action=cancel&order=12345
```

### Check Balance
```
key=YOUR_API_KEY&action=balance
```

## Cron Jobs

Add these to your crontab for automated background processing:

```bash
# Sync order statuses every minute
* * * * * php /path/to/prism-smm-panel/cron/order_sync.php >> /path/to/prism-smm-panel/storage/logs/cron.log 2>&1

# Check refill requests every 5 minutes
*/5 * * * * php /path/to/prism-smm-panel/cron/refill_checker.php >> /path/to/prism-smm-panel/storage/logs/cron.log 2>&1

# Verify payments every 5 minutes
*/5 * * * * php /path/to/prism-smm-panel/cron/payment_verify.php >> /path/to/prism-smm-panel/storage/logs/cron.log 2>&1
```

## Security

- **CSRF:** All POST requests require a valid CSRF token
- **XSS:** All user input is escaped with `htmlspecialchars()`
- **SQL Injection:** All queries use PDO prepared statements
- **Passwords:** Hashed with bcrypt (`password_hash`)
- **Rate Limiting:** Configurable IP-based rate limiting
- **Headers:** X-Content-Type-Options, X-Frame-Options, X-XSS-Protection
- **Sessions:** Server-side with configurable lifetime
- **Directory Protection:** `.htaccess` blocks access to sensitive directories

## License

MIT License
