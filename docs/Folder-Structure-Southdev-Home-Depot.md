# SouthDev Home Depot — Project Folder Structure

Custom PHP MVC e-commerce system (XAMPP / Hostinger).  
Entry point: `index.php` → `routes/` → `controllers/` → `models/` → `views/`.

> **Note:** `vendor/` (Composer packages), `assets/uploads/` (user images), and `.git/` are omitted from the detailed tree below.

---

## 1. High-level architecture

```
Browser
   │
   ▼
index.php  ──►  routes/web.php | routes/api.php
   │
   ▼
middleware/  (Auth / Role checks)
   │
   ▼
controllers/  ──►  models/  ──►  MySQL (config/database.php)
   │
   ▼
views/  +  includes/ (header, navbar, footer, sidebar)
   │
   ▼
assets/ (css, js, uploads)  |  payment/ (PayMongo)  |  templates/email/
```

---

## 2. Folder purpose summary

| Folder / File | Purpose |
|---------------|---------|
| `index.php` | Front controller / bootstrap |
| `config/` | App config, DB connection, constants |
| `routes/` | URL routing (`web.php`, `api.php`) |
| `middleware/` | Auth & role gates (Customer, Staff, SuperAdmin) |
| `controllers/` | Request handling / business orchestration |
| `models/` | Database entities & queries |
| `views/` | PHP UI templates by role |
| `includes/` | Shared layout (header, navbar, footer, Mailer) |
| `assets/` | CSS, JS, uploaded images |
| `database/` | SQL schema, migrations, seeds |
| `payment/` | PayMongo success/fail/webhook pages |
| `templates/email/` | HTML email templates |
| `storage/` | Local storage (e.g. mail logs) |
| `tools/` | Dev utilities & k6 load tests (local) |
| `docs/` | Thesis / documentation assets |
| `vendor/` | Composer dependencies (Dompdf, PHPMailer, etc.) |
| `deploy-hostinger.ps1` | FTP deploy script |

---

## 3. Full directory tree (application source)

```
southdev-home-depot/
|-- index.php
|-- .htaccess
|-- composer.json
|-- composer.lock
|-- .gitignore
|-- .env.example
|-- .env.deploy.example
|-- .env.hostinger.example
|-- README.md
|-- CONFIGURATION_GUIDE.md
|-- DEPLOYMENT_GUIDE.md
|-- DEPLOYMENT_CHECKLIST.md
|-- DEMO_ACCOUNTS.md
|-- PAYMONGO_SETUP.md
|-- EWALLET_API_SETUP.md
|-- flowcharts.html
|-- deploy-hostinger.ps1
|-- deploy-git-status.ps1
|-- deploy-full-ftp.py
|-- pack-for-hostinger.ps1
|
|-- config/
|   |-- config.php
|   |-- constants.php
|   |-- database.php
|   `-- .htaccess
|
|-- routes/
|   |-- web.php
|   `-- api.php
|
|-- middleware/
|   |-- AuthMiddleware.php
|   |-- CustomerMiddleware.php
|   |-- StaffMiddleware.php
|   |-- SuperAdminMiddleware.php
|   `-- .htaccess
|
|-- controllers/
|   |-- AuthController.php
|   |-- GoogleAuthController.php
|   |-- CartController.php
|   |-- DashboardController.php
|   |-- InventoryController.php
|   |-- LogController.php
|   |-- NotificationController.php
|   |-- OrderController.php
|   |-- PaymentController.php
|   |-- ProductController.php
|   |-- ReportController.php
|   |-- ReturnController.php
|   |-- ReviewController.php
|   |-- SettingsController.php
|   |-- UserController.php
|   `-- .htaccess
|
|-- models/
|   |-- User.php
|   |-- Role.php
|   |-- Product.php
|   |-- Category.php
|   |-- Cart.php
|   |-- Order.php
|   |-- OrderItem.php
|   |-- Payment.php
|   |-- PayMongoGateway.php
|   |-- Inventory.php
|   |-- StockMovement.php
|   |-- PriceHistory.php
|   |-- Notification.php
|   |-- Review.php
|   |-- ReturnRequest.php
|   |-- CancelRequest.php
|   |-- DamagedProduct.php
|   |-- SupplierRequest.php
|   |-- PasswordReset.php
|   |-- RateLimiter.php
|   |-- Setting.php
|   |-- Log.php
|   `-- .htaccess
|
|-- views/
|   |-- auth/
|   |   |-- login.php
|   |   |-- register.php
|   |   |-- admin-login.php
|   |   |-- forgot-password.php
|   |   |-- reset-password.php
|   |   |-- verify-email.php
|   |   |-- google-signing-in.php
|   |   `-- complete-google-account.php
|   |
|   |-- customer/
|   |   |-- dashboard.php
|   |   |-- products.php
|   |   |-- product-details.php
|   |   |-- cart.php
|   |   |-- checkout.php
|   |   |-- orders.php
|   |   |-- order-details.php
|   |   |-- track-order.php
|   |   |-- profile.php
|   |   |-- notifications.php
|   |   |-- product-reviews.php
|   |   |-- request-return.php
|   |   |-- about.php
|   |   |-- faqs.php
|   |   |-- locations.php
|   |   |-- featured-collections.php
|   |   |-- room-gallery.php
|   |   |-- blog.php
|   |   |-- product-inquiry.php
|   |   `-- products_alt.php
|   |
|   |-- staff/
|   |   |-- dashboard.php
|   |   |-- inventory.php
|   |   |-- manage-orders.php
|   |   |-- order-details.php
|   |   |-- manage-returns.php
|   |   |-- return-details.php
|   |   |-- cancel-requests.php
|   |   |-- damaged-products.php
|   |   |-- stock-movements.php
|   |   |-- supplier-requests.php
|   |   |-- price-history.php
|   |   |-- reports.php
|   |   |-- reviews.php
|   |   |-- update-status.php
|   |   `-- profile.php
|   |
|   |-- superadmin/
|   |   |-- dashboard.php
|   |   |-- manage-products.php
|   |   |-- edit-product.php
|   |   |-- manage-categories.php
|   |   |-- manage-users.php
|   |   |-- view-user.php
|   |   |-- system-settings.php
|   |   `-- logs.php
|   |
|   `-- errors/
|       |-- 403.php
|       |-- 404.php
|       `-- 500.php
|
|-- includes/
|   |-- header.php
|   |-- footer.php
|   |-- navbar.php
|   |-- sidebar.php
|   |-- auth_check.php
|   |-- Mailer.php
|   `-- DeliveryFee.php
|
|-- assets/
|   |-- css/
|   |   |-- style.css
|   |   |-- customer.css
|   |   |-- admin.css
|   |   |-- dashboard.css
|   |   |-- logs.css
|   |   `-- responsive.css
|   |-- js/
|   |   |-- main.js
|   |   |-- cart.js
|   |   |-- checkout.js
|   |   |-- charts.js
|   |   |-- product-detail.js
|   |   |-- product-name-marquee.js
|   |   `-- validation.js
|   `-- uploads/          (product images, logos)
|
|-- payment/
|   |-- payment-gateway.php
|   |-- payment-success.php
|   |-- payment-failed.php
|   `-- webhook.php
|
|-- templates/
|   `-- email/
|       |-- order-receipt.html
|       |-- order-status.html
|       |-- payment-failed.html
|       |-- return-status.html
|       `-- verify-email.html
|
|-- database/
|   |-- southdev.sql
|   |-- southdev_export.sql
|   |-- hostinger_import.sql
|   |-- seed_data.sql
|   |-- migration_v2.sql
|   |-- add_*.sql / create_*.sql   (incremental migrations)
|   `-- .htaccess
|
|-- storage/
|   `-- mails/
|
|-- tools/                  (local/dev only — excluded from FTP deploy)
|   |-- load-tests/         (k6 smoke / browse / stress)
|   `-- *.php               (migration helpers, checks, demos)
|
|-- docs/                   (thesis figures, ERD, guides)
|
`-- vendor/                 (Composer: PHPMailer, Dompdf, vlucas/phpdotenv, …)
```

---

## 4. Request flow (short)

1. User hits a URL → Apache/`index.php`
2. `routes/web.php` (or `api.php`) maps URL → controller method
3. Middleware checks session / role if needed
4. Controller uses Model(s) for DB work
5. Controller loads a View under `views/...`
6. View uses `includes/` for chrome; `assets/` for CSS/JS

---

## 5. Roles ↔ views

| Role | Main views folder |
|------|-------------------|
| Guest / Customer | `views/auth/`, `views/customer/` |
| Staff | `views/staff/` |
| Super Admin | `views/superadmin/` |

---

*Generated for thesis / system documentation — SouthDev Home Depot.*
