<?php
include "includes/db.php";
include "includes/header.php";
include "includes/navigation.php";
require_once __DIR__ . '/admin/includes/ecommerce/Order.php';
require_once __DIR__ . '/admin/includes/ecommerce/OrderItem.php';

// Prevent direct access if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: shop.php");
    exit;
}

// 1. Data Prep: Calculate totals before unsetting the cart
$cart = $_SESSION['cart'];
$total_amount = 0;
foreach ($cart as $item) {
    $total_amount += ($item['price'] * $item['quantity']);
}

// Ensure user session data exists
$user_id = $_SESSION['user_id'] ?? null;
$user_email = $_SESSION['user_email'] ?? 'customer@example.com';
$user_name = $_SESSION['username'] ?? 'Valued Customer';

if (!$user_id) {
    die("Error: You must be logged in to checkout.");
}

// 2. Database Logic: Save Order
$order = new Order($connection);
$order->create($user_id, $total_amount);
$order_id = $order->getOrderId();

$items_summary_html = ""; 
$ordered_items_list = []; // To display on page after cart is cleared

foreach ($cart as $item) {
    $orderItem = new OrderItem($connection);
    $orderItem->create($order_id, $item['product_id'], $item['quantity'], $item['price']);
    
    $line_item = "{$item['name']} (x{$item['quantity']}) - $" . number_format($item['price'] * $item['quantity'], 2);
    $items_summary_html .= "<li>$line_item</li>";
    $ordered_items_list[] = $line_item;
}

// 3. Email Logic: Mailtrap
$email_data = [
    "from" => ["email" => "no-reply@smartbarangay.auf", "name" => "Smart Barangay Shop"],
    "to" => [["email" => $user_email]],
    "subject" => "Order Confirmation #$order_id",
    "html" => "
        <h1>Thank you for your order, $user_name!</h1>
        <p>Your order <strong>#$order_id</strong> has been received and is being processed.</p>
        <h3>Summary:</h3>
        <ul>$items_summary_html</ul>
        <p><strong>Total Amount: $" . number_format($total_amount, 2) . "</strong></p>
    "
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://send.api.mailtrap.io/api/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($email_data));

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer 3e9cd1e13f5501dfa6a961f4e15133d5' // Replace with your token
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$email_response = curl_exec($ch);
curl_close($ch);

// 4. Clear Cart
unset($_SESSION['cart']);
?>

<div class="container">
    <div class="row">
        <div class="col-md-12 text-center">
            <div class="alert alert-success">
                <h2><span class="glyphicon glyphicon-ok"></span> Order Complete!</h2>
                <p>A confirmation email has been sent to <strong><?php echo htmlspecialchars($user_email); ?></strong>.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"><h4>Customer Information</h4></div>
                <div class="panel-body">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user_name); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
                    <p><strong>Customer ID:</strong> #<?php echo htmlspecialchars($user_id); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"><h4>Order Information</h4></div>
                <div class="panel-body">
                    <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                    <p><strong>Total Paid:</strong> $<?php echo number_format($total_amount, 2); ?></p>
                    <p><strong>Status:</strong> Pending Processing</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><h4>List of Ordered Products</h4></div>
                <table class="table">
                    <thead>
                        <tr><th>Product Details</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordered_items_list as $item_text): ?>
                            <tr><td><?php echo htmlspecialchars($item_text); ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center">
                <a href="shop.php" class="btn btn-primary btn-lg">Return to Catalog</a>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>