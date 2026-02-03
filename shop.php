<?php
include "includes/db.php";

/* Page Header and navigation */
include "includes/header.php";
include "includes/navigation.php";

require_once __DIR__ . '/admin/includes/ecommerce/Product.php';

// Initialize shopping cart session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add product to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Check if product already in cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        // Get product details
        $product = new Product($connection);
        $result = $product->find($product_id);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $_SESSION['cart'][$product_id] = [
                'product_id' => $row['product_id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'quantity' => $quantity
            ];
        }
    }
    header("Location: shop.php");
}
?>

<!-- Page Content -->
<div class="container">

    <div class="row">

        <!-- Products Column -->
        <div class="col-md-8">

            <h1 class="page-header">
                All Products
            </h1>

            <!-- Shopping Cart Widget -->
            <div class="panel panel-info">
                <div class="panel-heading">
                    <span class="glyphicon glyphicon-shopping-cart"></span> Shopping Cart
                </div>
                <div class="panel-body">
                    <p>
                        <strong>Items in Cart: <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></strong>
                    </p>
                    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) { ?>
                        <a href="cart.php" class="btn btn-primary btn-block">
                            <span class="glyphicon glyphicon-eye-open"></span> View Cart
                        </a>
                    <?php } else { ?>
                        <p class="text-muted">Your cart is empty.</p>
                    <?php } ?>
                </div>
            </div>
            <hr>

            <?php
            $product = new Product($connection);
            $fetch_products_data = $product->all();

            if ($fetch_products_data && mysqli_num_rows($fetch_products_data) > 0) {
                while ($row = mysqli_fetch_assoc($fetch_products_data)) {
                    $product_id = $row['product_id'];
                    $name = $row['name'];
                    $description = $row['description'];
                    $price = $row['price'];
                    $stock_quantity = $row['stock_quantity'];
                    $status = $row['status'];
                    $created_at = $row['created_at'];

                    // Only show active products
                    if ($status != 'active') {
                        continue;
                    }

                    $description_excerpt = substr($description, 0, 150) . "...";

                    ?>
                    <!-- Product Item -->
                    <h2>
                        <a href="#"><?php echo htmlspecialchars($name); ?></a>
                    </h2>
                    <p class="lead">
                        Price: <strong>$<?php echo number_format($price, 2); ?></strong>
                    </p>
                    <p><span class="glyphicon glyphicon-time"></span> Added on <?php echo $created_at; ?></p>
                    <p><span class="label label-info">In Stock: <?php echo $stock_quantity; ?></span></p>
                    <hr>
                    <p><?php echo htmlspecialchars($description_excerpt); ?></p>

                    <form method="post" action="">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="form-group" style="width: 150px;">
                            <label for="quantity">Quantity:</label>
                            <input type="number" class="form-control" name="quantity" value="1" min="1" max="<?php echo $stock_quantity; ?>">
                        </div>
                        <?php if ($stock_quantity > 0) { ?>
                            <button type="submit" name="add_to_cart" class="btn btn-success">
                                Add to Cart <span class="glyphicon glyphicon-shopping-cart"></span>
                            </button>
                        <?php } else { ?>
                            <button type="button" class="btn btn-danger" disabled>Out of Stock</button>
                        <?php } ?>
                    </form>

                    <hr>

                <?php }
            } else {
                echo "<p>No products available.</p>";
            }
            ?>

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
