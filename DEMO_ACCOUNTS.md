# Southdev Home Depot — Demo Accounts (Local)

These accounts are created in your local MySQL database (`southdev`) for testing.

> ⚠️ Security note: change these passwords before any public/production deployment.

## Login URL
- http://localhost/southdev-home-depot/index.php?url=login

## Accounts
### Customer
- **Email**: `customer@southdev.com`
- **Password**: `Customer@2026!`
- **Role**: customer 

### Staff Admin
- **Email**: `staff@southdev.com`
- **Password**: `Staff@2026!`
- **Role**: staff 

### Super Admin
- **Email**: `admin@southdev.com`
- **Password**: `SuperAdmin@2026!`
- **Role**: super_admin

### Inventory In-charge
- **Email**: `inventory@demo.local`
- **Password**: `Demo@1234`
- **Role**: inventory in-charge


## Reset a password (bcrypt)
Generate a bcrypt hash:

```bash
C:\xampp\php\php.exe -r "echo password_hash('NewPasswordHere!', PASSWORD_DEFAULT), PHP_EOL;"
```

Then update in MySQL:

```sql
UPDATE users
SET password = '$2y$10$...bcrypt_hash_here...', email_verified_at = NOW(), is_active = 1
WHERE email = 'admin@southdev.com';
```
