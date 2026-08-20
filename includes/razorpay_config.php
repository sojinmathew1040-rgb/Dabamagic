<?php
/**
 * Daba Magic - Razorpay Payment Gateway Configuration
 */

if (!defined('RAZORPAY_KEY_ID')) {
    // Default Razorpay Test Mode Credentials (replace with your live keys in production)
    define('RAZORPAY_KEY_ID', 'rzp_test_1DP5mmOlF5G5ag');
    define('RAZORPAY_KEY_SECRET', 's8K7U5zO9Q4s1L3o4P0q8R7t');
}

if (!defined('RAZORPAY_CURRENCY')) {
    // Default currency code (EUR for Daba Magic European operations, or INR/USD)
    define('RAZORPAY_CURRENCY', 'EUR');
}

if (!defined('RAZORPAY_COMPANY_NAME')) {
    define('RAZORPAY_COMPANY_NAME', 'Daba Magic Restaurant');
}

if (!defined('RAZORPAY_THEME_COLOR')) {
    define('RAZORPAY_THEME_COLOR', '#C86338'); // Terracotta brand color
}

/**
 * Helper to verify Razorpay signature for payment capture
 *
 * @param string $razorpay_order_id
 * @param string $razorpay_payment_id
 * @param string $razorpay_signature
 * @param string $secret
 * @return bool
 */
function verify_razorpay_signature($razorpay_order_id, $razorpay_payment_id, $razorpay_signature, $secret = RAZORPAY_KEY_SECRET) {
    if (empty($razorpay_signature) || empty($razorpay_payment_id)) {
        return false;
    }
    // If order_id was used
    if (!empty($razorpay_order_id)) {
        $expected_signature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, $secret);
        return hash_equals($expected_signature, $razorpay_signature);
    }
    return true;
}
