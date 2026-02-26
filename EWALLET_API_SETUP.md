# E-WALLET & PAYMENT API INTEGRATION GUIDE

**Status**: Manual Implementation (Current)  
**Next Phase**: Automated API Integration

---

## **CURRENT IMPLEMENTATION (Manual)**

Currently, the system uses **manual e-wallet payment** flow:

```
Customer → Selects GCash → Enters Reference # → Admin Reviews → Payment Completed
```

**Files**:
- [payment/payment-gateway.php](payment/payment-gateway.php) - Shows payment instructions
- [payment/payment-success.php](payment/payment-success.php) - Confirms payment
- [models/Payment.php](models/Payment.php) - Stores transaction data

---

## **OPTIONS FOR AUTOMATED E-WALLET INTEGRATION**

### **Option 1: PayMongo (Recommended)** ⭐ BEST

**Overview**: Full-featured payment gateway supporting GCash, Cards, Bank Transfers

**Supported Methods**:
- ✅ GCash (e-wallet)
- ✅ Grab Pay
- ✅ Credit/Debit Cards
- ✅ Bank Transfers
- ✅ BillEase (installments)

**Cost**: 2.2% - 3.5% per transaction  
**Setup Time**: 30 minutes  
**Complexity**: Medium

**Getting Started**:
```
1. Visit: https://paymongo.com
2. Sign up for merchant account
3. Get API keys (public & secret)
4. Complete KYC verification
5. Test in sandbox mode
6. Go live with live API keys
```

**PHP Integration**:
```php
// Install via Composer:
composer require paymongo/sdk

// Initialize client:
$client = new PayMongo\LaravelClient(env('PAYMONGO_SECRET_KEY'));

// Create payment source:
$source = $client->sources()->create([
    'amount' => 50000,  // in centavos
    'currency' => 'PHP',
    'type' => 'gcash',
    'redirect' => [
        'success' => 'https://yourdomain.com/payment/payment-success.php',
        'failed' => 'https://yourdomain.com/payment/payment-failed.php'
    ]
]);

// Get redirect URL:
$redirectUrl = $source->data['attributes']['redirect']['checkout_url'];
```

---

### **Option 2: Stripe** 

**Overview**: Global payment processor with GCash support

**Supported Methods**:
- ✅ Credit/Debit Cards
- ✅ GCash (Philippines)
- ✅ Bank Transfers
- ✅ Wallets (ApplePay, GooglePay)

**Cost**: 2.9% + ₱15 per transaction  
**Setup Time**: 1 hour  
**Complexity**: Medium-High

**Getting Started**:
```
1. Visit: https://stripe.com/ph
2. Create account
3. Verify bank account
4. Get API keys
5. Enable GCash payment method
```

---

### **Option 3: Direct GCash API**

**Overview**: Direct integration with GCash merchant portal

**Supported Methods**:
- ✅ GCash only

**Cost**: Direct negotiation with GCash  
**Setup Time**: 2-3 weeks  
**Complexity**: High

**Requirements**:
- DTI registration
- Business license
- Mayor's permit
- Bank account
- GCash merchant application

---

### **Option 4: 2Checkout (Verifone)**

**Overview**: Multi-currency, multi-payment processor

**Supported Methods**:
- ✅ GCash
- ✅ Credit Cards
- ✅ Bank Transfers
- ✅ Digital wallets

**Cost**: 2.5% - 3.5%  
**Setup Time**: 1 hour  
**Complexity**: Medium

---

## **🚀 IMPLEMENTATION: PayMongo (Step-by-Step)**

### **Step 1: Create PayMongo Account**

```
1. Go to https://paymongo.com
2. Click "Sign Up"
3. Enter business details
4. Verify email
5. Complete merchant profile
6. Wait for approval (usually same day)
```

### **Step 2: Install PayMongo SDK**

```bash
# Via Composer (requires SSH access):
cd /home/username/public_html
composer require paymongo/sdk

# OR manually download SDK files
# See: https://github.com/paymongo/paymongo-php
```

### **Step 3: Get API Keys**

In PayMongo Dashboard:
1. Go to "Settings" → "API Keys"
2. Copy:
   - **Public Key** (starts with pk_)
   - **Secret Key** (starts with sk_)
3. Keep these safe! Store in:
   - `config/config.php` or `.env`

```php
// config/config.php
define('PAYMONGO_SECRET_KEY', 'sk_live_xxxxxxxxxxxx');
define('PAYMONGO_PUBLIC_KEY', 'pk_live_xxxxxxxxxxxx');
```

### **Step 4: Create PayMongo Class**

Create new file: `models/PayMongoGateway.php`

```php
<?php
/**
 * PayMongo Payment Gateway Integration
 */

class PayMongoGateway {
    private $secretKey;
    private $publicKey;
    private $apiUrl = 'https://api.paymongo.com/v1';

    public function __construct() {
        $this->secretKey = PAYMONGO_SECRET_KEY;
        $this->publicKey = PAYMONGO_PUBLIC_KEY;
    }

    /**
     * Create payment source (GCash)
     */
    public function createGCashSource($amount, $description, $successUrl, $failureUrl) {
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => intval($amount * 100),  // Convert to centavos
                    'currency' => 'PHP',
                    'type' => 'gcash',
                    'redirect' => [
                        'success' => $successUrl,
                        'failed' => $failureUrl
                    ]
                ]
            ]
        ];

        return $this->makeRequest('POST', '/sources', $payload);
    }

    /**
     * Create payment (charge source)
     */
    public function createPayment($sourceId, $amount, $description) {
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => intval($amount * 100),
                    'currency' => 'PHP',
                    'source' => [
                        'id' => $sourceId,
                        'type' => 'source'
                    ],
                    'description' => $description
                ]
            ]
        ];

        return $this->makeRequest('POST', '/payments', $payload);
    }

    /**
     * Get payment details
     */
    public function getPayment($paymentId) {
        return $this->makeRequest('GET', "/payments/{$paymentId}", null);
    }

    /**
     * Make HTTP request to PayMongo API
     */
    private function makeRequest($method, $endpoint, $payload) {
        $curl = curl_init();
        
        $options = [
            CURLOPT_URL => $this->apiUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_USERPWD => $this->secretKey . ':',
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ];

        if ($method !== 'GET' && $payload) {
            $options[CURLOPT_POSTFIELDS] = json_encode($payload);
        }

        curl_setopt_array($curl, $options);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $decoded = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return $decoded;
        } else {
            throw new Exception('PayMongo API Error: ' . ($decoded['errors'][0]['detail'] ?? 'Unknown error'));
        }
    }
}
?>
```

### **Step 5: Update Payment Gateway View**

Update `payment/payment-gateway.php`:

```php
<?php
// ... existing code ...

elseif ($method === 'gcash'): ?>
    <div class="payment-info">
        <p>Redirecting to GCash payment gateway...</p>
        <div style="text-align:center; padding:2rem;">
            <i data-lucide="loader" style="animation: spin 1s linear infinite;"></i>
            <p style="margin-top:1rem; color:var(--steel);">Processing payment...</p>
        </div>
    </div>

    <script>
        // Create GCash payment source
        document.addEventListener('DOMContentLoaded', function() {
            fetch('<?= APP_URL ?>/controllers/PaymentController.php?action=create_gcash_source', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    order_id: <?= $orderId ?>,
                    amount: <?= $order['total_amount'] ?>
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.checkout_url) {
                    window.location.href = data.checkout_url;
                } else {
                    alert('Error: ' + (data.error || 'Payment initialization failed'));
                    window.location.href = '<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>';
                }
            })
            .catch(e => {
                alert('Network error: ' + e.message);
                window.location.href = '<?= APP_URL ?>/payment/payment-failed.php?order_id=<?= $orderId ?>';
            });
        });
    </script>

<?php endif; ?>
```

### **Step 6: Update PaymentController**

Add new methods to `controllers/PaymentController.php`:

```php
<?php

// Add to top of class
private $payMongoGateway;

public function __construct($pdo) {
    $this->paymentModel = new Payment($pdo);
    $this->orderModel   = new Order($pdo);
    $this->logModel     = new Log($pdo);
    
    // Initialize PayMongo if SDK available
    if (class_exists('PayMongoGateway')) {
        $this->payMongoGateway = new PayMongoGateway();
    }
}

/**
 * Create GCash payment source
 */
public function createGCashSource() {
    AuthMiddleware::handle();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    
    header('Content-Type: application/json');
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = intval($input['order_id'] ?? 0);
        $amount = floatval($input['amount'] ?? 0);
        
        // Verify order ownership
        $order = $this->orderModel->findById($orderId);
        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            throw new Exception('Invalid order');
        }
        
        // Create PayMongo source
        $source = $this->payMongoGateway->createGCashSource(
            $amount,
            "Order #{$order['order_number']}",
            APP_URL . '/payment/payment-success.php?order_id=' . $orderId,
            APP_URL . '/payment/payment-failed.php?order_id=' . $orderId
        );
        
        // Store payment source ID
        $paymentData = [
            'order_id' => $orderId,
            'payment_method' => 'gcash',
            'amount' => $amount,
            'status' => PAYMENT_PENDING,
            'transaction_id' => $source['data']['id']
        ];
        
        $this->paymentModel->create($paymentData);
        $this->logModel->create(LOG_PAYMENT, "GCash payment initiated for Order #{$orderId}");
        
        echo json_encode([
            'success' => true,
            'checkout_url' => $source['data']['attributes']['redirect']['checkout_url']
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

/**
 * Handle PayMongo webhook (payment confirmation)
 */
public function handlePayMongoWebhook() {
    header('Content-Type: application/json');
    
    try {
        $payload = file_get_contents('php://input');
        $event = json_decode($payload, true);
        
        // Verify webhook signature
        $signature = $_SERVER['HTTP_X_PAYMONGO_SIGNATURE'] ?? '';
        $expectedSignature = hash_hmac('sha256', $payload, PAYMONGO_SECRET_KEY);
        
        if (!hash_equals($signature, $expectedSignature)) {
            throw new Exception('Invalid signature');
        }
        
        // Handle payment.succeeded event
        if ($event['data']['type'] === 'payment.succeeded') {
            $paymentId = $event['data']['attributes']['id'];
            
            // Get full payment details
            $payment = $this->payMongoGateway->getPayment($paymentId);
            
            // Find and update order
            $sourceId = $payment['data']['attributes']['source']['id'];
            $dbPayment = $this->paymentModel->getBySourceId($sourceId);
            
            if ($dbPayment) {
                // Update payment status
                $this->paymentModel->updateStatus($dbPayment['id'], PAYMENT_COMPLETED, $paymentId);
                
                // Update order status
                $this->orderModel->updateStatus($dbPayment['order_id'], ORDER_PROCESSING);
                
                $this->logModel->create(LOG_PAYMENT, "GCash payment confirmed for Order #{$dbPayment['order_id']}");
            }
        }
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>
```

### **Step 7: Setup Webhook**

In PayMongo Dashboard:
1. Go to "Settings" → "Webhooks"
2. Click "Add Webhook"
3. URL: `https://yourdomain.com/payment/webhook.php`
4. Events: Select `payment.succeeded`, `payment.failed`
5. Enable webhook

Create `payment/webhook.php`:

```php
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/PaymentController.php';

$controller = new PaymentController($pdo);
$controller->handlePayMongoWebhook();
?>
```

### **Step 8: Update Payment Model**

Add to `models/Payment.php`:

```php
public function getBySourceId($sourceId) {
    $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE transaction_id = ?");
    $stmt->execute([$sourceId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
```

### **Step 9: Test Integration**

**Sandbox Testing**:
```
1. Use test API keys from PayMongo
2. Go through payment flow
3. Use test GCash credentials: 09xxxxxxxxx
4. Should see payment succeed
5. Check PayMongo dashboard for transaction
```

**Live Mode**:
```
1. Switch to live API keys
2. Test with small amount (₱1-10)
3. Verify in PayMongo dashboard
4. Monitor for errors in logs
```

---

## **CONFIGURATION REFERENCE**

### **config/config.php**

```php
// PayMongo Keys
define('PAYMONGO_SECRET_KEY', 'sk_live_your_secret_key_here');
define('PAYMONGO_PUBLIC_KEY', 'pk_live_your_public_key_here');

// Webhook Secret
define('PAYMONGO_WEBHOOK_SECRET', 'whk_your_webhook_secret_here');
```

### **Routes to Add**

Add to `routes/web.php`:

```php
// PayMongo
if (strpos($url, 'payment/create-gcash') === 0) {
    $controller = new PaymentController($pdo);
    $controller->createGCashSource();
}

if (strpos($url, 'payment/webhook') === 0) {
    $controller = new PaymentController($pdo);
    $controller->handlePayMongoWebhook();
}
```

---

## **TROUBLESHOOTING**

### **"cURL not enabled"**
```
Contact hosting provider to enable PHP cURL extension
Or ask them to add: extension=curl to php.ini
```

### **"SSL certificate error"**
```
Set CURLOPT_SSL_VERIFYPEER to false (development only)
Update CA certificates on production
```

### **"Invalid API key"**
```
Verify you're using correct key format (sk_live_xxxx)
Check key hasn't been rotated/regenerated
Make sure using secret key (not public key)
```

### **"Webhook not working"**
```
Verify webhook URL is publicly accessible
Check PayMongo webhook logs
Ensure firewall isn't blocking requests
Verify webhook secret matches in PayMongo
```

---

## **TIMELINE FOR SETUP**

| Task | Time |
|------|------|
| Register PayMongo | 30 mins |
| Get API keys | 5 mins |
| Install SDK | 10 mins |
| Code integration | 2 hours |
| Testing | 1 hour |
| Go live | 30 mins |
| **Total** | **~4 hours** |

---

## **NEXT STEPS**

1. **Choose provider**: PayMongo (recommended)
2. **Sign up**: Get API keys
3. **Install SDK**: Via Composer or manual
4. **Implement**: Follow integration steps above
5. **Test**: Use sandbox keys first
6. **Deploy**: Switch to live keys
7. **Monitor**: Watch PayMongo dashboard

---

## **ADDITIONAL RESOURCES**

- PayMongo Docs: https://docs.paymongo.com
- PayMongo PHP SDK: https://github.com/paymongo/paymongo-php
- Test API Keys: Available in PayMongo Settings
- Support Email: support@paymongo.com

---

**Status**: Ready for E-wallet API integration when needed!
