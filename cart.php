<?php

include "includes/db.php";
/* Page Header and navigation */
include "includes/header.php";
include "includes/navigation.php";

// Initialize shopping cart session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Remove product from cart
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart.php");
}

// Update product quantity in cart
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = (int)$quantity;
        }
    }
    header("Location: cart.php");
}

// Clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'])) {
        unset($_SESSION['cart']);
        error_log('Cleared the cart');
    }
    header("Location: cart.php");
}
?>

<!-- Page Content -->
<div class="container">

    <div class="row">

        <!-- Cart Column -->
        <div class="col-md-8">

            <h1 class="page-header">
                Shopping Cart
            </h1>

            <?php if (empty($_SESSION['cart'])) { ?>
                <div class="alert alert-info">
                    <p>Your cart is empty.</p>
                    <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            <?php } else { ?>
                <form method="post" action="">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cart_total = 0;
                            foreach ($_SESSION['cart'] as $product_id => $item) {
                                $subtotal = $item['price'] * $item['quantity'];
                                $cart_total += $subtotal;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <?php echo $item['quantity']; ?>
                                    </td>
                                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                                    <td>
                                        <a href="cart.php?remove=<?php echo $product_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">
                                            <span class="glyphicon glyphicon-trash"></span> Remove
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" style="text-align: right;">Cart Total:</th>
                                <th>$<?php echo number_format($cart_total, 2); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="form-group">
                        <a href="cart.php?clear=1" class="btn btn-warning" onclick="return confirm('Are you sure you want to clear the cart?');">
                            <span class="glyphicon glyphicon-trash"></span> Clear Cart
                        </a>
                        <a href="shop.php" class="btn btn-info">Continue Shopping</a>
                    </div>
                </form>
                
                <hr>

                <form action="checkout.php" method="POST">

                    <div class="well">
                        <h4>Proceed to Checkout</h4>
                        <p>Review your cart above and click the button below to proceed with your order.</p>

                        <button type="submit" class="btn btn-lg btn-success">
                            <span class="glyphicon glyphicon-credit-card"></span> Checkout
                        </button>
                    </div>
                </form>

            <?php } ?>

        </div>

        <?php
        include "includes/sidebar.php"
        ?>
    </div>
    <!-- /.row -->

    <hr>
    <?php
    /* Page Footer */
    include "includes/footer.php"
    ?>
