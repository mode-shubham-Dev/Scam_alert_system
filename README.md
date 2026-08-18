# ScamShield — Scam Alert System

A community-powered scam reporting and public alert system built with PHP and MySQL.
Users report scams they encounter, moderators review and verify them, awareness managers
publish public alerts, and admins manage the entire platform.

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.1+ (PDO, password_hash, session) |
| Database | MySQL 8.0 (Amazon RDS on AWS) |
| Frontend | Bootstrap 5.3, Bootstrap Icons, Inter font |
| Server | Apache 2.4 on Amazon EC2 (Ubuntu) |
| Storage | Local uploads folder (S3-ready) |

---

## Main Features

- Public scam alert board — visible to everyone without login
- Role-based access control — 4 distinct roles with separate dashboards
- Secure login — bcrypt password hashing, brute-force lockout after 5 attempts
- CSRF protection — every form is protected with CSRF tokens
- File upload validation — MIME type checked, random filename, PHP execution blocked in uploads
- Session hardening — HttpOnly, SameSite Strict, strict mode enabled
- HTTP security headers — X-Frame-Options, X-Content-Type-Options, Referrer-Policy
- Email subscription — visitors can subscribe to receive alert notifications
- Moderator notes — moderators can leave written reasons for their decisions
- Admin statistics dashboard — visual breakdown of reports by status and scam type
- Scam type filter — moderators can filter the review queue by category

---

## User Roles

There are 4 roles in the system. Each role has exactly 2 features.

### Reporter
| Feature | Description |
|---------|-------------|
| Submit Report | Report a scam with description, scam type, and optional image evidence |
| My Reports | View all submitted reports and their current status |

### Moderator
| Feature | Description |
|---------|-------------|
| Review Queue | View all pending reports and make a Verified or Rejected decision |
| Review History | View a log of all previously reviewed reports and decisions |

### Admin
| Feature | Description |
|---------|-------------|
| Dashboard | System-wide statistics — users, reports, alerts, scam type breakdown |
| Manage Users | Change any user's role or delete accounts |

### Awareness Manager
| Feature | Description |
|---------|-------------|
| Publish Alert | Write and publish a public scam warning to the community |
| Verified Reports | Browse all moderator-verified reports as reference before writing alerts |

---

## Project Structure

```
scam-alert-system/
├── config/
│   ├── config.php          ← Reads DB credentials from .env or environment variables
│   └── .htaccess           ← Blocks direct web access to config/
├── database/
│   ├── schema.sql          ← Run first — creates all 4 tables on a fresh database
│   └── migration.sql       ← Run second — adds hardening columns and indexes (idempotent)
├── public/                 ← Apache document root points here
│   ├── assets/style.css    ← Custom design system on top of Bootstrap
│   ├── uploads/            ← Evidence images (.htaccess blocks PHP execution here)
│   ├── index.php           ← Public landing page
│   ├── login.php / register.php / logout.php
│   ├── alerts.php          ← Public alerts list (no login needed)
│   ├── subscribe.php       ← Email subscription (no login needed)
│   ├── report_form.php     ← Reporter: submit report
│   ├── my_reports.php      ← Reporter: view own reports
│   ├── review_queue.php    ← Moderator: pending reports
│   ├── review_history.php  ← Moderator: reviewed reports log
│   ├── report_detail.php   ← Moderator: review individual report
│   ├── dashboard.php       ← Admin: statistics
│   ├── manage_users.php    ← Admin: user management
│   ├── publish_alert.php   ← Awareness Manager: publish alerts
│   └── verified_reports.php← Awareness Manager: view verified reports
├── src/
│   ├── auth.php            ← Session start, login, register, role enforcement
│   ├── db.php              ← PDO singleton
│   ├── security.php        ← CSRF helpers, upload validation, flash messages
│   └── views/
│       ├── header.php      ← Navbar, flash display
│       └── footer.php      ← Footer, Bootstrap JS
├── .env                    ← YOUR LOCAL CREDENTIALS — never committed
├── .env.example            ← Safe template — committed to Git
└── .gitignore
```

---

## Local Setup

### Requirements
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Apache with mod_rewrite enabled
- PHP extensions: `pdo_mysql`, `fileinfo`, `mbstring`

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/your-username/scam-alert-system.git
cd scam-alert-system
```

**2. Create your local .env file**
```bash
cp .env.example .env
```
Open `.env` and fill in your local MySQL credentials.

**3. Set up the database**

Open MySQL and run both files in order:
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p scam_alert_db < database/migration.sql
```

Or run each in phpMyAdmin or TablePlus SQL tab:
```sql
source database/schema.sql;
source database/migration.sql;
```

**4. Create your first admin account**

Go to the Register page in your browser and sign up with your email.
Then run this in your database client to promote it to admin:
```sql
UPDATE users SET role = 'admin' WHERE email = 'your@email.com';
```

Log in — you will land on the Admin Dashboard.
From Manage Users, promote other registered accounts to their correct roles.

**5. Point Apache to the public folder**

Your Apache `DocumentRoot` or `VirtualHost` should point to:
```
/path/to/scam-alert-system/public
```

---

## .env Configuration

```env
DB_HOST=127.0.0.1               # Local: 127.0.0.1 | AWS: your RDS endpoint
DB_PORT=3306
DB_NAME=scam_alert_db
DB_USER=your_database_user
DB_PASS=your_database_password
```

The app reads credentials in this order:
1. `$_ENV` set from `.env` file (local development)
2. `getenv()` system environment variables (AWS EC2 or Elastic Beanstalk)

---

## Database Setup

The `database/` folder contains two SQL files:

| File | Purpose | When to Run |
|------|---------|-------------|
| `schema.sql` | Creates all 4 tables (`users`, `reports`, `alerts`, `subscribers`) | Once on a fresh database |
| `migration.sql` | Adds security columns and performance indexes | After schema.sql — safe to re-run |

Tables created:
- **users** — accounts with roles, bcrypt password hash, brute-force lockout columns
- **reports** — scam reports with status, evidence path, MIME type, moderator notes
- **alerts** — published public scam warnings
- **subscribers** — email addresses subscribed to alert notifications

---

## AWS Deployment Overview

### Infrastructure
- **EC2** — Ubuntu server running Apache 2.4 and PHP 8.1
- **RDS** — Amazon RDS MySQL 8.0 (separate from EC2)
- **Apache Virtual Host** — serves `/var/www/html/scam-alert-system/public`

### Deployment Steps

**1. SSH into your EC2 instance**
```bash
ssh -i your-key.pem ubuntu@your-ec2-public-ip
```

**2. Pull the latest code**
```bash
cd /var/www/html/scam-alert-system
git pull origin main
```

**3. Set up .env on the server**
```bash
cp .env.example .env
nano .env
# Fill in your RDS endpoint and credentials
```

**4. Set correct file permissions**
```bash
sudo chown -R www-data:www-data /var/www/html/scam-alert-system
sudo chmod -R 755 /var/www/html/scam-alert-system
sudo chmod -R 775 /var/www/html/scam-alert-system/public/uploads
```

**5. Run database setup against RDS**
```bash
mysql -h your-rds-endpoint.amazonaws.com -u your_user -p scam_alert_db < database/schema.sql
mysql -h your-rds-endpoint.amazonaws.com -u your_user -p scam_alert_db < database/migration.sql
```

**6. Restart Apache**
```bash
sudo systemctl restart apache2
```

### Apache Virtual Host Example
```apache
<VirtualHost *:80>
    ServerName your-ec2-public-ip
    DocumentRoot /var/www/html/scam-alert-system/public

    <Directory /var/www/html/scam-alert-system/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/scamshield_error.log
    CustomLog ${APACHE_LOG_DIR}/scamshield_access.log combined
</VirtualHost>
```

---

## Security Notes

- **Never commit your `.env` file.** It is listed in `.gitignore`. Your real database password stays local and on the server only.
- **Never put RDS credentials, EC2 IPs, AWS access keys, or PEM key names inside any PHP file, SQL file, or the README.**
- The `config/config.php` file reads credentials from environment variables only. No credentials are hardcoded.
- The `public/uploads/.htaccess` blocks PHP execution inside the uploads directory.
- The `config/.htaccess` blocks all direct web access to the config folder.
- CSRF tokens protect every POST form in the application.
- Passwords are hashed with bcrypt at cost 12 — never stored in plain text.

---

## GitHub Update Workflow

After making changes locally:

```bash
# Check what changed
git status

# Stage your changes
git add .

# Commit with a message
git commit -m "describe what you changed"

# Push to GitHub
git push origin main
```

After pushing, SSH into your EC2 instance and pull:

```bash
ssh -i your-key.pem ubuntu@your-ec2-public-ip
cd /var/www/html/scam-alert-system
git pull origin main
sudo systemctl restart apache2
```

---

## Test Accounts (Development Only)

> These credentials are for local development and assignment testing only.
> Change all passwords before any real public deployment.

| Role | Email | Password |
|------|-------|----------|
| Admin | karnashubham72@gmail.com | ScamShield@123 |
| Reporter | reporter@test.com | ScamShield@123 |
| Moderator | moderator@test.com | ScamShield@123 |
| Awareness Manager | manager@test.com | ScamShield@123 |

---

## User Manual Summary

| Role | Lands On After Login | Feature 1 | Feature 2 |
|------|---------------------|-----------|-----------|
| Reporter | Submit Report | Submit scam reports with evidence | View own reports and their status |
| Moderator | Review Queue | Review and decide on pending reports | View full history of all reviewed reports |
| Admin | Dashboard | System statistics and scam type charts | Manage all user accounts and roles |
| Awareness Manager | Publish Alert | Write and publish public scam alerts | Read verified reports as reference |

---

*ScamShield — PHP Assignment Project | AWS EC2 + RDS Deployment*
