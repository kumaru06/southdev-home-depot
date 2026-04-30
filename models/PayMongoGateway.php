<?php
/**
 * SouthDev Home Depot – PayMongo Payment Gateway Integration
 * Handles GCash, Card Payments, and Bank Transfers via PayMongo API
 */

class PayMongoGateway {
    private $secretKey;
    private $publicKey;
    private $apiUrl = 'https://api.paymongo.com/v1';
    private $webhookSecret;

    public function __construct() {
        $this->secretKey = PAYMONGO_SECRET_KEY;
        $this->publicKey = PAYMONGO_PUBLIC_KEY;
        $this->webhookSecret = PAYMONGO_WEBHOOK_SECRET;
    }

    /**
     * Create payment source (GCash, Card, etc.)
     * 
     * @param float $amount Order amount in PHP
     * @param string $type 'gcash', 'card', 'bank_transfer'
     * @param string $description Order/transaction reference
     * @param string $successUrl Redirect on success
     * @param string $failureUrl Redirect on failure
     * @return array Response with checkout URL
     */
    public function createSource($amount, $type, $description, $successUrl, $failureUrl) {
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => intval($amount * 100),  // Convert PHP to centavos
                    'currency' => 'PHP',
                    'type' => $type,
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
     * Create payment/charge from source
     * 
     * @param string $sourceId Source ID from createSource
     * @param float $amount Amount in PHP
     * @param string $description Reference
     * @return array Payment response with ID
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
                    'description' => $description,
                    'statement_descriptor' => 'SOUTHDEV HOME DEPOT'
                ]
            ]
        ];

        return $this->makeRequest('POST', '/payments', $payload);
    }

    /**
     * Get payment details
     *
     * @param string $paymentId Payment ID from checkout
     * @return array Payment details with status
     */
    public function getPayment($paymentId) {
        return $this->makeRequest('GET', "/payments/{$paymentId}", null);
    }

    /**
     * Get source details
     *
     * @param string $sourceId  src_xxx
     * @return array Source data with status and attributes
     */
    public function getSource($sourceId) {
        return $this->makeRequest('GET', "/sources/{$sourceId}", null);
    }

    /**
     * Generate a static QRPh code (Scan to Pay)
     * Endpoint: POST /v1/qrph/generate
     *
     * @return array Response containing qr_image (base64 PNG) and qr_string
     */
    public function generateQrph() {
        $payload = [
            'data' => [
                'attributes' => [
                    'kind' => 'instore'
                ]
            ]
        ];
        return $this->makeRequest('POST', '/qrph/generate', $payload);
    }

    /**
     * Create a Checkout Session (supports qrph, gcash, card, grab_pay, etc.)
     * PayMongo hosts the checkout page with a per-transaction QR code.
     *
     * @param float  $amount       Order amount in PHP
     * @param array  $lineItems    [ ['name'=>..., 'quantity'=>..., 'amount'=>..., 'currency'=>'PHP'] ]
     * @param array  $methodTypes  e.g. ['qrph'], ['gcash'], ['card','qrph']
     * @param string $successUrl   Redirect after successful payment
     * @param string $cancelUrl    Redirect if customer cancels
     * @param string $description  Short description
     * @return array Checkout session data (checkout_url, id, etc.)
     */
    public function createCheckoutSession($amount, $lineItems, $methodTypes, $successUrl, $cancelUrl, $description = '') {
        $payload = [
            'data' => [
                'attributes' => [
                    'send_email_receipt'  => false,
                    'show_description'    => true,
                    'show_line_items'     => true,
                    'payment_method_types' => $methodTypes,
                    'line_items'          => $lineItems,
                    'amount'              => intval($amount * 100),
                    'currency'            => 'PHP',
                    'description'         => $description,
                    'success_url'         => $successUrl,
                    'cancel_url'          => $cancelUrl,
                ]
            ]
        ];
        return $this->makeRequest('POST', '/checkout_sessions', $payload);
    }

    /**
     * Retrieve a Checkout Session by ID
     *
     * @param string $sessionId  cs_xxx
     * @return array Checkout session data with payment_intent, status, etc.
     */
    public function getCheckoutSession($sessionId) {
        return $this->makeRequest('GET', "/checkout_sessions/{$sessionId}", null);
    }

    // ─── CARD PAYMENT (Payment Intent flow) ───────────────────────────────────

    /**
     * Create a Payment Intent for card payments
     * 
     * @param float  $amount      Order amount in PHP
     * @param string $description Order/transaction reference
     * @return array Response containing id, client_key, status
     */
    public function createPaymentIntent($amount, $description) {
        $payload = [
            'data' => [
                'attributes' => [
                    'amount'                   => intval($amount * 100),
                    'currency'                 => 'PHP',
                    'capture_type'             => 'automatic',
                    'description'              => $description,
                    'statement_descriptor'     => 'SOUTHDEV HOME DEPOT',
                    'payment_method_allowed'   => ['card'],
                    'payment_method_options'   => [
                        'card' => ['request_three_d_secure' => 'any']
                    ]
                ]
            ]
        ];

        return $this->makeRequest('POST', '/payment_intents', $payload);
    }

    /**
     * Attach a Payment Method to a Payment Intent
     * Called from backend after frontend creates the PaymentMethod via JS.
     *
     * @param string $paymentIntentId  pi_xxx
     * @param string $paymentMethodId pm_xxx (created by PayMongo.js on frontend)
     * @param string $clientKey       client_key from the Payment Intent
     * @param string $returnUrl       URL to redirect after 3DS
     * @return array Updated Payment Intent (check status & next_action)
     */
    public function attachPaymentMethod($paymentIntentId, $paymentMethodId, $clientKey, $returnUrl) {
        $payload = [
            'data' => [
                'attributes' => [
                    'payment_method' => $paymentMethodId,
                    'client_key'     => $clientKey,
                    'return_url'     => $returnUrl
                ]
            ]
        ];

        return $this->makeRequest('POST', "/payment_intents/{$paymentIntentId}/attach", $payload);
    }

    /**
     * Retrieve a Payment Intent (e.g., after 3DS redirect to check final status)
     *
     * @param string $paymentIntentId  pi_xxx
     * @param string $clientKey        client_key (required for public-key auth retrieval)
     * @return array Payment Intent data
     */
    public function getPaymentIntent($paymentIntentId, $clientKey = null) {
        $endpoint = "/payment_intents/{$paymentIntentId}";
        if ($clientKey) {
            $endpoint .= '?client_key=' . urlencode($clientKey);
        }
        return $this->makeRequest('GET', $endpoint, null);
    }

    /**
     * Verify webhook signature
     * 
     * @param string $payload Raw webhook payload
     * @param string $signature X-Paymongo-Signature header
     * @return bool True if valid
     */
    public function verifyWebhookSignature($payload, $signature) {
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Make HTTPS request to PayMongo API
     * 
     * @param string $method GET, POST, etc.
     * @param string $endpoint API endpoint
     * @param array $payload Request body
     * @return array Decoded JSON response
     * @throws Exception on error
     */
    private function makeRequest($method, $endpoint, $payload) {
        $curl = curl_init();

        $options = [
            CURLOPT_URL => $this->apiUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_USERPWD => $this->secretKey . ':',
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: SouthDev-Home-Depot/1.0'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ];

        if ($method !== 'GET' && $payload) {
            $options[CURLOPT_POSTFIELDS] = json_encode($payload);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        // Handle cURL errors
        if ($curlError) {
            throw new Exception("PayMongo Connection Error: {$curlError}");
        }

        $decoded = json_decode($response, true);

        // Handle API errors
        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = 'Unknown error';
            if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                $errorMsg = $decoded['errors'][0]['detail'] ?? $errorMsg;
            } elseif (isset($decoded['error'])) {
                $errorMsg = $decoded['error'];
            }
            throw new Exception("PayMongo API Error ({$httpCode}): {$errorMsg}");
        }

        return $decoded;
    }
}
?>
