<?php
/**
 * Daba Magic - API: Create Razorpay Order
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

// Read raw JSON input
$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload received.']);
    exit;
}

$customer = $data['customer'] ?? [];
$items = $data['items'] ?? [];

$customer_name = trim($customer['name'] ?? '');
$customer_email = trim($customer['email'] ?? '');
$customer_phone = trim($customer['phone'] ?? '');
$order_type = trim($customer['order_type'] ?? 'Delivery');
$table_number = trim($customer['table_number'] ?? '');
$delivery_address = trim($customer['delivery_address'] ?? '');
$order_notes = trim($customer['order_notes'] ?? '');

if (empty($customer_name) || empty($customer_phone) || empty($items)) {
    echo json_encode(['success' => false, 'error' => 'Customer name, phone number, and at least one dish item are required.']);
    exit;
}

// Calculate and verify pricing on server
$subtotal = 0.00;
$validated_items = [];

foreach ($items as $it) {
    $dish_name = trim($it['name'] ?? 'Food Item');
    $dish_price = floatval($it['price'] ?? 0.00);
    $qty = intval($it['quantity'] ?? 1);
    if ($qty < 1) $qty = 1;

    $item_total = round($dish_price * $qty, 2);
    $subtotal += $item_total;

    $validated_items[] = [
        'dish_id' => intval($it['id'] ?? 0),
        'dish_name' => $dish_name,
        'dish_price' => $dish_price,
        'quantity' => $qty,
        'item_total' => $item_total
    ];
}

$subtotal = round($subtotal, 2);
$tax = round($subtotal * 0.09, 2); // 9% restaurant hospitality VAT
$delivery_fee = ($order_type === 'Delivery') ? 3.50 : 0.00;
$total_amount = round($subtotal + $tax + $delivery_fee, 2);

// Generate unique Order Number
$order_number = 'DM-ORD-' . date('ymd') . '-' . strtoupper(substr(uniqid(), 8)) . rand(10, 99);
$razorpay_order_id = 'rzp_ord_' . strtoupper(substr(md5($order_number), 0, 14));

// Insert into tbl_orders
$stmt = $con->prepare("INSERT INTO tbl_orders (
    order_number, customer_name, customer_email, customer_phone, 
    order_type, table_number, delivery_address, order_notes, 
    subtotal, tax, delivery_fee, total_amount, 
    payment_method, payment_status, order_status, razorpay_order_id
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Razorpay', 'Pending', 'Received', ?)");

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Database prepare failed: ' . $con->error]);
    exit;
}

$stmt->bind_param(
    "ssssssssdddds",
    $order_number, $customer_name, $customer_email, $customer_phone,
    $order_type, $table_number, $delivery_address, $order_notes,
    $subtotal, $tax, $delivery_fee, $total_amount,
    $razorpay_order_id
);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Failed to create order record: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$order_db_id = $stmt->insert_id;
$stmt->close();

// Insert items into tbl_order_items
$item_stmt = $con->prepare("INSERT INTO tbl_order_items (order_id, dish_id, dish_name, dish_price, quantity, item_total) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($validated_items as $v_item) {
    $item_stmt->bind_param("iisdid", $order_db_id, $v_item['dish_id'], $v_item['dish_name'], $v_item['dish_price'], $v_item['quantity'], $v_item['item_total']);
    $item_stmt->execute();
}
$item_stmt->close();

// Prepare response payload for Razorpay Checkout JS
$amount_in_cents = intval(round($total_amount * 100));

echo json_encode([
    'success' => true,
    'order_id' => $order_db_id,
    'order_number' => $order_number,
    'razorpay_order_id' => $razorpay_order_id,
    'amount' => $amount_in_cents,
    'total_formatted' => '€' . number_format($total_amount, 2),
    'currency' => RAZORPAY_CURRENCY,
    'key_id' => RAZORPAY_KEY_ID,
    'customer' => [
        'name' => $customer_name,
        'email' => $customer_email,
        'contact' => $customer_phone
    ],
    'theme' => [
        'color' => RAZORPAY_THEME_COLOR
    ],
    'company' => [
        'name' => RAZORPAY_COMPANY_NAME,
        'description' => 'Food Order #' . $order_number
    ]
]);
