<?php

include "includes/db.php";
/* Page Header and navigation */
include "includes/header.php";
include "includes/navigation.php";
require_once __DIR__ . '/admin/includes/ecommerce/Order.php';
require_once __DIR__ . '/admin/includes/ecommerce/OrderItem.php';

// Initialize shopping cart session if not exists
if (!isset($_SESSION['cart'])) {
    throw new \Exception('The cart is empty');
}
// missing customer user_id
if (!isset($_SESSION['user_id'])) {
    throw new \Exception('Missing customer ID');
}

$cart = $_SESSION['cart'];

$total_amount = 0;
foreach ($cart as $item) {
    $total_amount += ($item['price'] * $item['quantity']);
}

$order = new Order($connection);
$order->create(
    $_SESSION['user_id'],
    $total_amount
);

$items = [];
foreach ($cart as $item) {
    $orderItem = new OrderItem($connection);
    $orderItem->create(
        $order->getOrderId(),
        $item['product_id'],
        $item['quantity'],
        $item['price']
    );
}
unset($_SESSION['cart']);
?>

<h1>Order Complete</h1>