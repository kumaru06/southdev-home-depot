# CONFIGURATION SETUP GUIDE

> **Before deploying, update these 2 files with your server details**

---

## **File 1: `config/config.php`**

### What to update:

**Lines 1-4** - Application settings:
```php
<?php
define('APP_URL', 'https://yourdomain.com');  // YOUR DOMAIN HERE
define('APP_NAME', 'SouthDev Home Depot');
define('APP_ENV', 'production');
define('APP_DEBUG', false);
```

**If using subdirectory:**
```php
define('APP_URL', 'https://yourdomain.com/store');
```

**If using HTTP (not recommended):**
```php
define('APP_URL', 'http://yourdomain.com');
```

**PayMongo Configuration** (Optional - for automated e-wallet):
```php
// Get API keys from: https://dashboard.paymongo.com/settings/api-keys
define('PAYMONGO_ENABLED', false);  // Set to true to enable PayMongo
define('PAYMONGO_SECRET_KEY', 'sk_test_xxxxxxxxxxxx');  // Your secret key
define('PAYMONGO_PUBLIC_KEY', 'pk_test_xxxxxxxxxxxx');  // Your public key
define('PAYMONGO_WEBHOOK_SECRET', 'whk_test_xxxxxxxxxxxx');  // Your webhook secret
```

See **PAYMONGO_SETUP.md** for detailed PayMongo instructions.

---

## **File 2: `config/database.php`**

### What to update:

**Lines 5-9** - Database credentials:
```php
$dbHost = 'localhost';              // Your database host
$dbPort = 3306;                     // Usually 3306
$dbName = 'southdev_home_depot';    // Your database name
$dbUser = 'southdev_user';          // Your database user
$dbPass = 'SecurePassword123';      // Your database password
```

### How to get these values:

**If using cPanel:**
1. Login to cPanel
2. Go to "MySQL Databases"
3. You'll see:
   - Database name created: `prefix_southdev_home_depot`
   - Username created: `prefix_southdev_user`
   - Password: what you entered
4. Host is always: `localhost`

**If using Plesk:**
1. Login to Plesk
2. Go to "Databases"
3. Find your database
4. Click on it to see credentials

**If using SSH:**
```bash
# List all databases:
mysql -u root -p
> SHOW DATABASES;

# Show database users:
> SELECT user, host FROM mysql.user;
```

---

## **Optional: Email Configuration** (for future use)

If you want to enable email features later, update `config/config.php`:

```php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-specific-password');
define('MAIL_FROM_EMAIL', 'noreply@yourdomain.com');
define('MAIL_FROM_NAME', 'SouthDev Home Depot');
```

---

## **Verification Checklist**

After updating the configuration files:

- [ ] APP_URL is correct (with https://)
- [ ] Database host is set to `localhost`
- [ ] Database name matches what you created
- [ ] Database user matches what you created
- [ ] Database password is correct
- [ ] All files are saved
- [ ] Files are uploaded to server

---

## **Test Configuration**

After uploading, test the connection:

### **Method 1: Via Website**
1. Visit `https://yourdomain.com/` 
2. If it loads without error, database is connected ✓
3. Try to register a new account
4. Check phpMyAdmin - new user should appear in `users` table

### **Method 2: Via phpMyAdmin**
1. Login to phpMyAdmin
2. Click on database: `southdev_home_depot`
3. You should see 15 tables
4. Click on `users` table → see data

### **Method 3: Via SSH** (if available)
```bash
mysql -h localhost -u southdev_user -p southdev_home_depot
# Enter password when prompted

mysql> SELECT COUNT(*) FROM users;
mysql> SHOW TABLES;
mysql> EXIT;
```

---

## **Common Configuration Errors & Solutions**

### ❌ Error: "Could not connect to database"
**Cause**: Wrong credentials in `config/database.php`
**Solution**:
1. Double-check username in cPanel MySQL Databases
2. Make sure password is typed exactly (watch for spaces)
3. Ensure database exists (check `$dbName`)
4. Try `localhost` as host first

### ❌ Error: "Access denied for user"
**Cause**: User or password incorrect
**Solution**:
1. Verify the exact username (cPanel shows it)
2. Reset password in cPanel if forgotten
3. Verify user is assigned to database with ALL privileges

### ❌ Error: "Unknown database"
**Cause**: Database name typo
**Solution**:
1. Check exact database name in cPanel
2. Some hosts add prefix (e.g., `username_southdev`)
3. Copy-paste the exact name from cPanel

### ❌ Error: "APP_URL mismatch"
**Cause**: Domain not matching
**Solution**:
1. Make sure APP_URL uses your actual domain
2. Include `https://` 
3. Don't include trailing slash
4. If subdirectory, include it: `https://yourdomain.com/store`

---

## **Security Notes** 🔒

- Never use weak passwords (use 12+ characters with mixed case, numbers, symbols)
- Never commit credentials to version control
- Always use HTTPS in production (not HTTP)
- Change default credentials before going live
- Keep database user separate from root user
- Use strong, unique passwords for admin accounts

---

## **Ready to Deploy?**

Once you've updated both config files:

1. ✅ Save the files
2. ✅ Upload to server
3. ✅ Visit `https://yourdomain.com` in browser
4. ✅ If it loads, you're ready!
5. ✅ Follow DEPLOYMENT_CHECKLIST.md for next steps

**Questions?** See DEPLOYMENT_GUIDE.md for detailed instructions.
