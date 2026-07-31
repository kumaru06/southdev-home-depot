# SouthDev Home Depot — System Features & Tech Stack

PHP e-commerce platform for home improvement products (tiles & hardware supply).

---

## User Roles

| Role | Description |
|------|-------------|
| **Customer** | Browse, order, track, return, review |
| **Staff Admin** | Manage orders, returns, inventory view, reports, reviews |
| **Super Admin (Owner)** | Full system management: users, products, categories, settings, logs |
| **Inventory In-charge** | Manage stock, supplier requests, movements, price history, damaged products |

---

## System Features

### Authentication & Account
- Login / Register
- Separate admin login
- Google Sign-In (OAuth + complete account flow)
- Email verification (OTP + link) with resend
- Forgot / reset password
- Username & email availability check
- Profile update
- Email change via OTP verification
- Logout with role-based access middleware

### Security
- Google reCAPTCHA v2 on login, admin login, and register
- CSRF protection
- Login rate limiting / lockout
- OTP attempt lockout
- Session ID regeneration (anti-session fixation)
- Security headers (CSP, X-Frame-Options, X-Content-Type-Options, XSS, Referrer-Policy, Permissions-Policy)
- reCAPTCHA is skipped when keys are empty (local/dev friendly)

### Customer / Storefront
- Product browsing, search, and product details
- Categories and featured collections
- Room gallery, blog, about, FAQs
- Store locations with Google Maps
- Product inquiry page
- Shopping cart (add / update / remove)
- Checkout with shipping address (prefill from profile)
- Order history, order details, track order
- Cancel request
- Return request (proof photo upload, item-level selection)
- Product reviews (after delivery)
- In-app notifications (read / mark all read)
- Cart and order API endpoints

### Payments
- Cash on Delivery (COD)
- GCash (manual reference flow + PayMongo)
- Bank transfer
- Card payments
- QRPh checkout
- Payment success / failed pages
- Payment status polling and webhooks
- Payment marked as refunded when a return is completed
- Order receipt and order status emails

**Order statuses:** `pending` → `processing` → `shipped` → `delivered` / `cancelled`

**Payment statuses:** `pending`, `completed`, `failed`, `refunded`

### Staff Admin
- Dashboard
- Manage orders and update status
- Cancel requests (approve / reject)
- Inventory monitoring
- Supplier requests
- Return requests
- Damaged products
- Reports
- Reviews management
- Live sidebar badges (pending orders, cancels, returns, suppliers, low stock)

### Inventory In-charge
- Dashboard
- Manage stock (update quantity / add stock)
- Supplier requests (update status + receive stock)
- Stock movements history
- Price history
- Damaged products (status updates)
- Reports
- Low-stock alerts and badges

### Super Admin
- Dashboard
- All orders and cancel requests
- Manage users (create, update, activate/deactivate, reset password, delete, view)
- Manage products (CRUD + bulk delete)
- Manage categories
- Inventory and supplier requests
- Returns and damaged products
- Reviews
- System logs (audit trail)
- Reports
- System settings (general + payment)

### Reports & Exports
- Sales reports (daily / monthly / detailed)
- Current inventory report
- Inventory added / combined reports
- Damaged inventory report
- PDF export (Dompdf)
- CSV export handlers

### Supporting Features
- Activity / audit logging
- Rate limiter
- Email HTML templates (order receipt, order status)
- File uploads (products, return proof, favicon, etc.)
- Role-based sidebar navigation
- Error pages (403 / 404 / 500)
- Pagination

---

## Tech Stack & Tools

### Backend
| Tool | Purpose |
|------|---------|
| PHP 7.4+ | Main application language |
| Custom MVC | Controllers, Models, Views, Routes, Middleware |
| MySQL + PDO | Database access |
| Composer | PHP dependency manager |
| vlucas/phpdotenv | Environment configuration (`.env`) |
| PHPMailer | SMTP transactional email |
| Dompdf | PDF report generation |
| Apache + `.htaccess` | Web server and URL routing |

### Frontend
| Tool | Purpose |
|------|---------|
| HTML / CSS / Vanilla JavaScript | UI and client-side behavior |
| Custom CSS | Design system (`style.css`, `responsive.css`, role styles) |
| Google Fonts (Inter, Plus Jakarta Sans) | Typography |
| Lucide Icons | Icon set |
| Chart.js | Dashboard and report charts |
| Mermaid | System flowchart documentation (`flowcharts.html`) |

### Integrations & Services
| Tool | Purpose |
|------|---------|
| PayMongo API | GCash, card, bank, QRPh, webhooks |
| Google OAuth | Sign-In with Google |
| Google reCAPTCHA v2 | Bot protection on auth forms |
| Google Maps | Store locations page |
| SMTP (e.g. Gmail) | Verification, password reset, order emails |

### Architecture & Ops
| Item | Notes |
|------|--------|
| Role-based access control | 4 roles with dedicated middleware |
| Session authentication | PHP sessions |
| Environment configs | `.env`, `.env.example`, Hostinger examples |
| Deployment | Hostinger/FTP scripts and deployment guides |
| Documentation | README, PayMongo setup, e-wallet guide, configuration guide |

---

## Quick Module Map

```
Auth        → login, register, Google, OTP, reCAPTCHA, password reset
Store       → products, cart, checkout, track order
Orders      → create, status updates, cancel requests
Payments    → COD, GCash, bank, card, QRPh, PayMongo
Returns     → request, proof upload, approve/complete, refund status
Inventory   → stock, movements, price history, damaged, supplier requests
Admin       → users, products, categories, settings, logs
Reports     → sales & inventory PDF/CSV exports
Security    → CSRF, rate limit, reCAPTCHA, CSP headers
```

---

## Notes

- Dedicated Cashier/POS screen is mentioned in high-level docs but is not a separate module in the current codebase.
- Wishlist, coupons/promos, and live chat are not implemented.
- GrabPay / BillEase appear in PayMongo docs as possible options but are not in the current payment method constants (`cod`, `gcash`, `bank`, `card`).
