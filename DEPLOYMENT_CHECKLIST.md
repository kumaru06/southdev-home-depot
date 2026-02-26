# DEPLOYMENT QUICK CHECKLIST 🚀

**Estimated Time**: 2-3 hours total  
**Difficulty**: Easy (follow steps in order)

---

## **PRE-DEPLOYMENT (This Week)**

- [ ] Contact hosting provider, confirm specifications
- [ ] Register domain name (or use existing)
- [ ] Purchase SSL certificate (or use Let's Encrypt - free)
- [ ] Get MySQL credentials from hosting
- [ ] Backup any existing database
- [ ] Read DEPLOYMENT_GUIDE.md thoroughly

---

## **DEPLOYMENT DAY CHECKLIST**

### **PHASE 1: Upload Files** (30 mins)
- [ ] Download all project files
- [ ] Connect to server via SFTP/FTP or cPanel
- [ ] Upload to `public_html/` directory
- [ ] Verify all files transferred (check file count)
- [ ] Verify folder structure is correct

### **PHASE 2: Database Setup** (20 mins)
- [ ] Open phpMyAdmin or MySQL control panel
- [ ] Create database: `southdev_home_depot`
- [ ] Create user: `southdev_user` with strong password
- [ ] Grant ALL privileges to user
- [ ] Import `database/southdev.sql` file
- [ ] OPTIONAL: Import `database/seed_data.sql` (demo data)

### **PHASE 3: Configuration** (15 mins)
- [ ] Edit `config/config.php`:
  ```php
  define('APP_URL', 'https://yourdomain.com');
  ```
- [ ] Edit `config/database.php`:
  ```php
  $dbHost = 'localhost';
  $dbUser = 'southdev_user';
  $dbPass = '[your password]';
  $dbName = 'southdev_home_depot';
  ```
- [ ] Save both files and re-upload

### **PHASE 4: Permissions & Apache** (15 mins)
- [ ] Set directory permissions to 755
- [ ] Set file permissions to 644
- [ ] Set uploads folder to 777: `chmod 777 assets/uploads/`
- [ ] Enable mod_rewrite in cPanel or SSH
- [ ] Verify `.htaccess` file exists

### **PHASE 5: SSL Setup** (10 mins)
- [ ] Go to cPanel AutoSSL or Let's Encrypt
- [ ] Request certificate for your domain
- [ ] Wait for issuance (usually instant)
- [ ] Verify HTTPS works: visit https://yourdomain.com

### **PHASE 6: Create Admin Account** (5 mins)
**Option A - Registration:**
- [ ] Visit https://yourdomain.com/index.php?url=register
- [ ] Create account with admin email
- [ ] Login to phpMyAdmin
- [ ] Change user's `role_id` to `1`

**Option B - Direct Database:**
- [ ] In phpMyAdmin
- [ ] Insert admin user with bcrypt-hashed password
- [ ] Set `role_id = 1`

---

## **POST-DEPLOYMENT TESTING (30 mins)**

### **Critical Tests** 🔴
- [ ] Website loads: https://yourdomain.com
- [ ] Registration page works
- [ ] Login works with new account
- [ ] Product page displays
- [ ] Contact database (check in phpMyAdmin)

### **Important Tests** 🟡
- [ ] Add to cart function works
- [ ] Checkout page validates form
- [ ] Payment page displays (all 3 methods)
- [ ] Admin dashboard loads
- [ ] Search products works

### **Nice-to-Have Tests** 🟢
- [ ] Images display correctly
- [ ] CSS styling applied
- [ ] Responsive on mobile (F12 Dev Tools)
- [ ] Console shows no JavaScript errors
- [ ] Order tracking works

---

## **TROUBLESHOOTING QUICK FIXES**

### **"500 Internal Server Error"** → Check logs, verify database credentials

### **"Database connection failed"** → 
```
1. Verify hostname (usually 'localhost')
2. Verify username/password
3. Verify database exists
4. Try from SSH: mysql -u user -p
```

### **"Page not found (404)"** → 
```
1. Check .htaccess exists
2. Enable mod_rewrite
3. Verify APP_URL in config.php
```

### **"CSS/JS not loading"** → 
```
1. Check APP_URL matches actual domain
2. Clear browser cache (Ctrl+Shift+Del)
3. Check file permissions (644)
```

### **"Images not showing"** → 
```
1. Check assets/uploads/ folder exists and has files
2. Check permissions (755 for folder, 644 for files)
3. Verify image paths in database
```

---

## **AFTER GOING LIVE**

### **Day 1:**
- [ ] Monitor error logs: tail -f /var/log/apache2/error.log
- [ ] Test payment transactions with small amount
- [ ] Get user feedback
- [ ] Check database backup created

### **Week 1:**
- [ ] Verify all features working as expected
- [ ] Monitor performance
- [ ] Create automated backups
- [ ] Update admin password (if using default)

### **Ongoing:**
- [ ] Regular backups (weekly/monthly)
- [ ] Monitor error logs
- [ ] Keep PHP/MySQL updated
- [ ] Review user feedback

---

## **REQUIRED INFORMATION BEFORE DEPLOYMENT**

```
Hosting Provider: _________________
Control Panel: ___________________
cPanel Username: __________________
Hosting Account Password: XXXXXX
MySQL Hostname: ___________________
MySQL Username: ____________________
MySQL Password: XXXXXX
Domain Name: _____________________
Server IP Address: __________________
SSH Port: __________________
Support Email: _____________________
```

---

## **FILES TO UPLOAD** ✓

```
✓ index.php
✓ .htaccess
✓ config/ (3 files)
✓ controllers/ (11 files)
✓ models/ (12 files)
✓ views/ (25+ files)
✓ includes/ (5 files)
✓ middleware/ (1 file)
✓ routes/ (1 file)
✓ assets/ (10+ files)
✓ payment/ (3 files)
✓ database/ (SQL files)
Total: 60+ files
```

---

## **SUCCESS INDICATORS** ✅

After deployment, you should see:

1. **Home page loads** with products visible
2. **Footer** shows SouthDev Home Depot branding
3. **Navigation bar** has links
4. **Colors** match the design (charcoal, red accent)
5. **Icons** from Lucide display correctly
6. **Login page** has CSRF token
7. **Admin panel** shows dashboard with charts
8. **Database** has all 15 tables populated

---

## **ESTIMATED TIME BREAKDOWN**

| Phase | Time | Status |
|-------|------|--------|
| Upload files | 10 mins | ⏱️ |
| Database setup | 10 mins | ⏱️ |
| Configuration | 10 mins | ⏱️ |
| Permissions | 10 mins | ⏱️ |
| SSL setup | 10 mins | ⏱️ |
| Create admin | 5 mins | ⏱️ |
| Testing | 30 mins | ⏱️ |
| **TOTAL** | **2.5 hours** | ✅ |

---

## **IF SOMETHING GOES WRONG**

1. **Take a screenshot** of the error
2. **Check error logs**: 
   ```bash
   tail -f /var/log/apache2/error.log
   ```
3. **Check database**: Is it accessible from phpMyAdmin?
4. **Verify credentials**: Are they correct in config.php?
5. **Contact hosting support** with the error message

---

**You've got this! 🚀**

*Questions? See DEPLOYMENT_GUIDE.md for detailed instructions*
