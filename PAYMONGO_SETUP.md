# PayMongo Integration Setup Guide ✅

**Status**: Code integrated and ready to configure  
**Difficulty**: Easy (5 steps, ~30 minutes)

---

## **Quick Setup**

### **Step 1: Get PayMongo Account** (5 mins)

1. Go to **https://paymongo.com**
2. Click **Sign Up**
3. Fill in:
   - Business/Shop name: `Southdev Home Depot`
   - Email: your email
   - Phone: your phone
4. Verify email
5. Wait for approval (usually same day)

---

### **Step 2: Get API Keys** (2 mins)

In PayMongo Dashboard:
1. Go to **Settings** → **API Keys**
2. You'll see two versions:
   - **Test Keys** (for testing)
   - **Live Keys** (for production)

**Copy these** (we'll need them in Step 3):
- Secret Key (starts with `sk_test_` or `sk_live_`)
- Public Key (starts with `pk_test_` or `pk_live_`)
- Webhook Secret (starts with `whk_test_` or `whk_live_`)

---

### **Step 3: Update Configuration** (5 mins)

Edit **`config/config.php`** around line 15:

```php
// PayMongo Configuration
define('PAYMONGO_ENABLED', true);  // Set to false to disable
define('PAYMONGO_SECRET_KEY', 'sk_test_xxxxxxxxxxxx');  // PASTE YOUR SECRET KEY HERE
define('PAYMONGO_PUBLIC_KEY', 'pk_test_xxxxxxxxxxxx');  // PASTE YOUR PUBLIC KEY HERE
define('PAYMONGO_WEBHOOK_SECRET', 'whk_test_xxxxxxxxxxxx');  // PASTE YOUR WEBHOOK SECRET HERE
```

**Example (filled):**
```php
define('PAYMONGO_SECRET_KEY', 'sk_test_6B2Q0YKyrgUvLaKxz3wJ1');
define('PAYMONGO_PUBLIC_KEY', 'pk_test_7h8K3mL9qW2eR5t0yU1V');
define('PAYMONGO_WEBHOOK_SECRET', 'whk_test_4Z5X6C7V8B9N0M1L2');
```

Save the file.

---

### **Step 4: Add Webhook in PayMongo** (5 mins)

In PayMongo Dashboard:
1. Go to **Settings** → **Webhooks**
2. Click **Add Webhook**
3. Fill in:
   - **URL**: `https://yourdomain.com/payment/webhook.php`
   - **Events**: Select these:
     - ✅ `payment.succeeded`
     - ✅ `payment.failed`
4. Click **Save**

**For local testing**, use ngrok (uncommon):
```
https://abc123.ngrok.io/payment/webhook.php
```

---

### **Step 5: Test Integration** (10 mins)

**Local Testing (Before Deploying):**

1. Keep `PAYMONGO_ENABLED = true` in config
2. Use `sk_test_` and `pk_test_` keys (test keys)
3. Visit your app:
   - Create an order
   - Select GCash payment
   - Should see "Preparing payment..."
   - Will try to redirect to test GCash (you can close it)
4. In PayMongo Dashboard, check **Transactions** to see the test event

**What you should see:**
- ✅ "Preparing payment..." loading message
- ✅ Redirect attempt to test payment
- ✅ Transaction appears in PayMongo Dashboard after 5 seconds

---

## **DATABASE UPDATE REQUIRED** ⚠️

The database now has a new column `source_id` in payments table.

**If deploying to new server:**
- Just import `database/southdev.sql` (already has the column)
- Done!

**If updating existing database:**
Run this SQL query via phpMyAdmin:

```sql
ALTER TABLE payments 
ADD COLUMN source_id VARCHAR(255) AFTER transaction_id,
ADD INDEX idx_source_id (source_id);
```

---

## **PAYMENT FLOW**

### **With PayMongo Enabled:**
```
Customer selects GCash
         ↓
JavaScript calls PayMongo API
         ↓
PayMongo creates payment source
         ↓
Redirects to GCash app
         ↓
Customer completes payment in GCash
         ↓
Webhook arrives (payment.succeeded)
         ↓
Order automatically marked as paid
         ↓
Order status → Processing
```

### **If PayMongo is Disabled:**
```
Customer selects GCash
         ↓
Manual payment form
         ↓
Customer enters reference #
         ↓
Customer clicks submit
         ↓
Admin verifies in dashboard
         ↓
Order manually marked as paid
```

---

## **USEFUL TROUBLESHOOTING**

### ❌ **"Payment initialization failed"**
- Check API keys in config.php
- Make sure keys match (test keys with test mode, live keys with live)
- Verify PAYMONGO_ENABLED = true

### ❌ **"Webhook not working"**
- Check webhook URL is correct (include https://)
- Make sure firewall allows PayMongo IP
- Verify webhook secret matches
- Check PayMongo Dashboard → Webhooks → Logs

### ❌ **"Payment shows pending"**
- Wait 5-10 seconds (webhook takes time)
- Check database: `SELECT * FROM payments ORDER BY id DESC LIMIT 1;`
- Check logs: `SELECT * FROM logs WHERE action LIKE '%payment%' ORDER BY id DESC LIMIT 5;`

### ✅ **"How do I know if it worked?"**
- Check PayMongo Dashboard → Transactions
- Should show your test transaction
- If green checkmark = payment succeeded

---

## **UPDATING API KEYS LATER**

To switch from test to live keys later:

1. In PayMongo Dashboard:
   - Generate live keys
   - Verify bank account

2. In `config/config.php`:
   - Replace `sk_test_` with `sk_live_`
   - Replace `pk_test_` with `pk_live_`
   - Replace `whk_test_` with `whk_live_`

3. Update webhook URL in PayMongo Dashboard (live version)

4. Test with small amount (₱1-10)

5. You're live!

---

## **DISABLING PAYMONGO**

If you want to go back to manual payments:

Edit `config/config.php`:
```php
define('PAYMONGO_ENABLED', false);  // Disable PayMongo
```

Now GCash payments will use the manual form again.

---

## **API KEY FORMATS**

**Test Keys** (for development):
- Start with: `sk_test_`, `pk_test_`, `whk_test_`
- Used for testing
- No real money involved

**Live Keys** (for production):
- Start with: `sk_live_`, `pk_live_`, `whk_live_`
- Used for real payments
- Real money involved

Never mix test and live keys!

---

## **VERIFICATION CHECKLIST**

Before going live:

- [ ] PayMongo account created
- [ ] API keys obtained
- [ ] Webhook secret obtained
- [ ] config/config.php updated with keys
- [ ] Database updated with source_id column
- [ ] Webhook added in PayMongo Dashboard
- [ ] Test payment attempted
- [ ] Transaction appears in PayMongo Dashboard
- [ ] Webhook logs show payment.succeeded event
- [ ] Order status changed to "Processing"
- [ ] All PAYMONGO_ENABLED still = true

---

## **SUPPORT**

**PayMongo Support**: https://support.paymongo.com  
**Documentation**: https://docs.paymongo.com/  
**Status Page**: https://status.paymongo.com

---

## **NEXT STEPS**

1. ✅ Create PayMongo account
2. ✅ Get API keys
3. ✅ Update config/config.php
4. ✅ Update database
5. ✅ Add webhook in PayMongo
6. ✅ Test with test keys
7. 🚀 Deploy next week
8. 🚀 Switch to live keys after deployment

**You're ready!** 🎉

All PayMongo code is integrated. Just fill in the API keys and you're good to go.
