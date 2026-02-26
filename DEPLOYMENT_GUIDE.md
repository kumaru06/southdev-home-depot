# SouthDev Home Depot - Deployment Guide 🚀

**Status**: ✅ Production Ready  
**Version**: 1.0  
**Date**: February 14, 2026

---

## **📋 PRE-DEPLOYMENT CHECKLIST (This Week)**

### **Week 1: Preparation Phase**
- [ ] **Day 1-2**: Gather server details (host, cPanel/control panel, MySQL info)
- [ ] **Day 2-3**: Prepare all deployment files
- [ ] **Day 3-4**: Test database backup/restore locally
- [ ] **Day 4-5**: Create domain and SSL certificate (if needed)
- [ ] **Day 5**: Final code review and security audit
- [ ] **Day 6-7**: Prepare deployment checklist and schedule

---

## **🖥️ SERVER REQUIREMENTS**

### **Minimum Specifications:**
```
PHP:        7.4 or higher (8.0+ recommended)
MySQL:      5.7 or MariaDB 10.3+
Apache:     2.4+ with mod_rewrite enabled
Storage:    500MB+ available space
Memory:     512MB minimum
SSL:        HTTPS certificate (recommended)
```

### **Required PHP Extensions:**
- ✅ PDO
- ✅ PDO_MySQL
- ✅ OpenSSL
- ✅ Mbstring
- ✅ Curl
- ✅ GD (for image processing)

### **Verify on Your Server:**
```bash
php -m  # Lists all installed modules
php -v  # Check PHP version
```

---

## **📁 DEPLOYMENT FILES STRUCTURE**

```
public_html/
├── index.php
├── .htaccess
├── config/
│   ├── config.php
│   ├── constants.php
│   └── database.php
├── controllers/
│   └── [all 11 controllers]
├── models/
│   └── [all 12 models]
├── views/
│   ├── auth/
│   ├── customer/
│   ├── staff/
│   ├── superadmin/
│   └── errors/
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   ├── sidebar.php
│   └── auth_check.php
├── middleware/
│   └── AuthMiddleware.php
├── routes/
│   └── web.php
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   ├── admin.css
│   │   ├── customer.css
│   │   ├── dashboard.css
│   │   └── responsive.css
│   ├── js/
│   │   ├── main.js
│   │   ├── cart.js
│   │   ├── checkout.js
│   │   ├── validation.js
│   │   └── charts.js
│   ├── uploads/
│   │   └── [product images]
│   └── images/
├── database/
│   ├── southdev.sql
│   └── seed_data.sql
└── payment/
    ├── payment-gateway.php
    ├── payment-success.php
    └── payment-failed.php
```

---

## **🚀 DEPLOYMENT STEPS (Day 1: 2-3 hours)**

### **Step 1: Upload Files to Server** (30 mins)
```bash
Method 1: FTP
- Use FileZilla or Cyberduck
- Connect to your server via SFTP/FTP
- Upload all files to public_html/

Method 2: cPanel File Manager
- Login to cPanel
- Go to File Manager
- Navigate to public_html/
- Upload files (or extract zip)

Method 3: SSH (if available)
cd /home/username/public_html
git clone <repository-url> .
# or
scp -r * user@server:/home/username/public_html/
```

### **Step 2: Create Database** (15 mins)
```bash
Via cPanel:
1. Go to "MySQL Databases"
2. Create new database: southdev_home_depot
3. Create new user: southdev_user
4. Set password (strong: Mix of upper, lower, numbers, symbols)
5. Add user to database with ALL privileges

Via phpMyAdmin:
1. Login to phpMyAdmin
2. Click "New" database
3. Enter name: southdev_home_depot
4. Click "Create"
5. Go to "User accounts" → "Add user account"
6. Username: southdev_user
7. Host: localhost
8. Password: [strong password]
9. Grant all privileges on southdev_home_depot.*
```

### **Step 3: Import Database Schema** (15 mins)
```bash
Via phpMyAdmin:
1. Select southdev_home_depot database
2. Click "Import" tab
3. Choose file: database/southdev.sql
4. Click "Go"

Via SSH/Command Line:
mysql -u southdev_user -p southdev_home_depot < database/southdev.sql
# Enter password when prompted
```

### **Step 4: Seed Demo Data (Optional)** (5 mins)
```bash
# If you have sample data to populate
mysql -u southdev_user -p southdev_home_depot < database/seed_data.sql
```

### **Step 5: Configure Environment** (20 mins)

**Edit `config/config.php`:**
```php
// Line 3 - Set your domain:
define('APP_URL', 'https://yourdomain.com');

// OR if in subdirectory:
define('APP_URL', 'https://yourdomain.com/store');

// Line 4 - Set application name (optional):
define('APP_NAME', 'SouthDev Home Depot');
```

**Edit `config/database.php`:**
```php
// Lines 5-9 - Update database credentials:
$dbHost = 'localhost';           // Usually 'localhost'
$dbUser = 'southdev_user';       // MySQL username
$dbPass = 'your_strong_password'; // MySQL password
$dbName = 'southdev_home_depot'; // Database name

// If using different host:
$dbHost = '192.168.1.xxx'; // or IP address
```

### **Step 6: Configure File Permissions** (10 mins)
```bash
# Via SSH:
chmod 755 /home/username/public_html/          # Root directory
chmod 755 /home/username/public_html/views     # All directories
chmod 755 /home/username/public_html/assets
chmod 755 /home/username/public_html/uploads   # Image uploads
chmod 644 /home/username/public_html/index.php # PHP files
chmod 644 /home/username/public_html/*.php

# Make uploads writable:
chmod 777 /home/username/public_html/assets/uploads/

# Via cPanel File Manager:
1. Right-click folder
2. Change Permissions
3. Set to 755 (directories) or 644 (files)
```

### **Step 7: Enable mod_rewrite (Apache)** (5 mins)

**Via cPanel:**
1. Go to "EasyApache" or "MultiPHP Manager"
2. Ensure Apache is selected
3. Click "Select PHP Version"
4. Ensure "mod_rewrite" is checked
5. Save & rebuild

**Or manually (SSH):**
```bash
a2enmod rewrite
systemctl restart apache2
```

**Verify `.htaccess` exists in public_html:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>
```

### **Step 8: SSL Certificate** (10 mins)
```bash
Via cPanel:
1. Go to "AutoSSL" or "Let's Encrypt for cPanel"
2. Select your domain
3. Click "Issue" or "Auto-renew"
4. Wait for confirmation

# Test SSL:
https://yourdomain.com should show padlock
```

### **Step 9: Create Admin Account** (5 mins)

**Option A: Via Database (Direct Insert)**
```sql
-- Login to phpMyAdmin
INSERT INTO users (first_name, last_name, email, password, role_id, is_active, created_at)
VALUES (
    'Admin',
    'User',
    'admin@yourdomain.com',
    '$2y$10$[BCRYPT_HASH]',  -- Use bcrypt hash (see below)
    1,  -- ROLE_SUPER_ADMIN
    1,
    NOW()
);

-- Generate bcrypt hash in PHP:
echo password_hash('SecurePassword123!', PASSWORD_DEFAULT);
```

**Option B: Via Registration Page** (recommended)
1. Visit `https://yourdomain.com/index.php?url=register`
2. Create account with admin email
3. Via phpMyAdmin, change `role_id` to `1` (SUPER_ADMIN)

---

## **✅ POST-DEPLOYMENT VERIFICATION (Day 1: 30 mins)**

### **Test Checklist:**

**1. Website Accessibility** ✓
```
- [ ] https://yourdomain.com loads
- [ ] All pages load (no 404 errors)
- [ ] Images display correctly
- [ ] CSS/styling is applied
- [ ] JavaScript works (check browser console)
```

**2. Authentication** ✓
```
- [ ] Registration works
- [ ] Login works
- [ ] Password hashing works (check DB)
- [ ] Logout works
- [ ] Session management works
```

**3. Customer Features** ✓
```
- [ ] Product catalog displays
- [ ] Search functionality works
- [ ] Category filtering works
- [ ] Add to cart works
- [ ] Checkout form validates
- [ ] Payment methods display
```

**4. Payment System** ✓
```
- [ ] COD payment works (→ payment-success.php)
- [ ] GCash payment form displays
- [ ] Bank transfer form displays
- [ ] CSRF tokens present on forms
```

**5. Admin Features** ✓
```
- [ ] Login as admin
- [ ] Dashboard loads with charts
- [ ] Can manage products
- [ ] Can manage users
- [ ] Logs are recorded
```

**6. Database** ✓
```
- [ ] Check phpMyAdmin
- [ ] Verify all 15 tables created
- [ ] Check constraints exist
- [ ] Test a query: SELECT COUNT(*) FROM users;
```

**7. Error Handling** ✓
```
- [ ] Visit non-existent page → Error 404 displays
- [ ] Check browser console for JS errors
- [ ] Check server error log: tail -f /var/log/apache2/error.log
```

---

## **📊 MONITORING & MAINTENANCE**

### **Daily Tasks:**
- Check error logs
- Monitor disk space
- Check backup status

### **Weekly Tasks:**
- Review user activity logs
- Check payment transactions
- Monitor inventory levels

### **Monthly Tasks:**
- Database optimization: `OPTIMIZE TABLE users, products, orders;`
- Backup database
- Review performance metrics

### **Access Logs:**
```bash
# Apache error log:
tail -f /var/log/apache2/error.log

# Application logs (in database):
SELECT * FROM logs ORDER BY created_at DESC LIMIT 50;

# System logs:
tail -f /var/log/apache2/access.log
```

---

## **🔒 SECURITY CHECKLIST**

- [ ] Change all default passwords
- [ ] Enable HTTPS (Let's Encrypt)
- [ ] Disable directory listing: `Options -Indexes`
- [ ] Hide PHP version: `expose_php = Off`
- [ ] Set strong database password
- [ ] Backup sensitive files
- [ ] Test SQL injection (should fail)
- [ ] Test CSRF protection
- [ ] Monitor for suspicious activity
- [ ] Keep PHP/MySQL updated

---

## **💾 BACKUP STRATEGY**

### **Automated Backups (Weekly):**
```bash
# Via cPanel: Home → Backups → Store Backup
# Or set cron job:
0 2 * * 0 /usr/local/bin/mysqldump -u southdev_user -p'password' southdev_home_depot | gzip > /home/backups/backup_$(date +%Y%m%d).sql.gz
```

### **Manual Backup:**
```bash
# Database:
mysqldump -u southdev_user -p southdev_home_depot > backup_$(date +%Y%m%d).sql

# Files:
tar -czf backup_$(date +%Y%m%d).tar.gz /home/username/public_html/
```

---

## **📞 SUPPORT RESOURCES**

### **Common Issues & Solutions:**

**Issue**: "500 Internal Server Error"
```
Solution:
1. Check PHP error log
2. Verify database credentials in config/database.php
3. Check file permissions
4. Disable PHP errors in production
```

**Issue**: "Database connection failed"
```
Solution:
1. Verify database username/password
2. Check database host (localhost vs IP)
3. Verify user has privileges
4. Test via SSH: mysql -u user -p -h host dbname
```

**Issue**: "mod_rewrite not working"
```
Solution:
1. Enable mod_rewrite: a2enmod rewrite
2. Verify .htaccess exists
3. Check DirectoryIndex: index.php
4. Restart Apache: systemctl restart apache2
```

**Issue**: "Images not uploading"
```
Solution:
1. Check permissions on assets/uploads (777)
2. Check file size limits in php.ini
3. Check GD extension enabled
4. Verify image format (JPG, PNG, GIF)
```

---

## **📅 DEPLOYMENT TIMELINE**

```
WEEK 1 (Next Week):
┌─────────────────────────────────────┐
│ Monday - Wednesday (Prep)           │
│ • Gather server credentials         │
│ • Test database locally             │
│ • Prepare deployment package        │
└─────────────────────────────────────┘
         ↓
┌─────────────────────────────────────┐
│ Thursday (Deployment Day)           │
│ • Upload files (8:00 AM)            │
│ • Create database (8:30 AM)         │
│ • Configure environment (9:00 AM)   │
│ • Test all features (10:00 AM)      │
│ • Final verification (11:00 AM)     │
│ • Go Live! (12:00 PM)               │
└─────────────────────────────────────┘
         ↓
┌─────────────────────────────────────┐
│ Friday (Monitoring)                 │
│ • Monitor error logs                │
│ • Check database integrity          │
│ • Test payment transactions         │
│ • User feedback collection          │
└─────────────────────────────────────┘
```

---

## **📞 QUICK CONTACT**

**Hosting Support**: [Your Hosting Provider]  
**Domain Registrar**: [Your Registrar]  
**Database Admin**: [Your Contact]  

---

## **✅ FINAL SIGN-OFF**

- [ ] All files uploaded
- [ ] Database configured
- [ ] Environment variables set
- [ ] Tests passed
- [ ] Backups created
- [ ] Users notified
- [ ] Go live confirmed

**Deployment Status**: ✅ **READY TO DEPLOY**

---

*Generated: February 14, 2026*  
*SouthDev Home Depot - Enterprise E-Commerce Platform*
