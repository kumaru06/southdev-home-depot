# SouthDev Home Depot

## Description
A PHP-based e-commerce platform for home improvement products.

## Features
- Customer product browsing and ordering
- Shopping cart and checkout
- Order management and tracking
- Return request system
- Staff order and inventory management
- Super admin system management
- Payment gateway integration

## Setup
1. Import `database/southdev.sql` into MySQL
2. Configure `config/database.php` with your credentials
3. Install dependencies: `composer require phpmailer/phpmailer`
4. Configure mail settings in `config/config.php` (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_EMAIL`)
5. Point your web server to the project root
6. Access via browser

## Developer
- **Errant Kram Perez**
- **?**
- **?**

## Description
A PHP-based e-commerce platform for Southdev Home Depot, Home Improvement


## Roles
- **Customer** - Browse, order, track, return
- **Staff** - Manage orders, returns, reports, cashier
- **Super Admin** - Manage users, products, categories, settings
- **Inventory In-charge** - Manage stock, Inventory monitoring

## Local Demo Accounts
For security, My repo does not store plain-text passwords.

If you need local demo accounts (customer/staff/super admin/inventory in-charge), create them in your local database and set your own passwords.