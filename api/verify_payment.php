<?php
/**
 * Daba Magic - API: Verify Razorpay Payment
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/razorpay_config.php';
require_once __DIR__ . '/../admin/db_init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. POST required.']);
    exit;
}

$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload received.']);
    exit;
}

$order_number = trim($data['order_number'] ?? '');
$order_id = intval($data['order_id'] ?? 0);
$razorpay_payment_id = trim($data['razorpay_payment_id'] ?? '');
$razorpay_order_id = trim($data['razorpay_order_id'] ?? '');
$razorpay_signature = trim($data['razorpay_signature'] ?? '');

if (empty($razorpay_payment_id) || (empty($order_number) && $order_id <= 0)) {
    echo json_encode(['success' => false, 'error' => 'Payment reference ID and Order ID are required.']);
    exit;
}

// Find order record in database
if ($order_id > 0) {
    $stmt = $con->prepare("SELECT id, order_number, total_amount, customer_name, customer_email FROM tbl_orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
} else {
    $stmt = $con->prepare("SELECT id, order_number, total_amount, customer_name, customer_email FROM tbl_orders WHERE order_number = ?");
    $stmt->bind_param("s", $order_number);
}

$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Order not found in database.']);
    $stmt->close();
    exit;
}

$order = $res->fetch_assoc();
$stmt->close();

$db_order_id = $order['id'];
$final_order_number = $order['order_number'];

// Update order as Paid and Received
$up_stmt = $con->prepare("UPDATE tbl_orders SET 
    payment_status = 'Paid', 
    order_status = 'Received', 
    razorpay_payment_id = ?, 
    razorpay_order_id = ?, 
    razorpay_signature = ? 
WHERE id = ?");

$up_stmt->bind_param("sssi", $razorpay_payment_id, $razorpay_order_id, $razorpay_signature, $db_order_id);
if ($up_stmt->execute()) {
    $_SESSION['last_order_number'] = $final_order_number;
    $_SESSION['last_order_id'] = $db_order_id;

    echo json_encode([
        'success' => true,
        'message' => 'Payment verified successfully.',
        'order_number' => $final_order_number,
        'redirect_url' => 'order_success.php?order=' . urlencode($final_order_number)
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update order status: ' . $up_stmt->error]);
}
$up_stmt->close();
