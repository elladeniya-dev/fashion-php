<?php
session_start();
if (!isset($_SESSION['sid'])) {
    header('Location: login.html');
    exit();
}

require_once __DIR__ . '/../../../config/database.php';

$customerLoginId = (int) $_SESSION['sid'];

// Resolve customer id
$customerId = null;
$stmt = $conn->prepare('SELECT cid FROM customer_login_details WHERE sid = ? LIMIT 1');
$stmt->bind_param('i', $customerLoginId);
if ($stmt->execute()) {
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $customerId = (int)$row['cid'];
    }
    $res->free();
}
$stmt->close();

if (!$customerId) {
    echo 'Customer not found for this account.';
    exit();
}

$message = '';

// Handle order request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));

    // Fetch product data
    $pstmt = $conn->prepare('SELECT price, stock_qty, status FROM products WHERE id = ? LIMIT 1');
    $pstmt->bind_param('i', $pid);
    $pstmt->execute();
    $pRes = $pstmt->get_result();
    $product = $pRes->fetch_assoc();
    $pRes->free();
    $pstmt->close();

    if (!$product || $product['status'] !== 'approved') {
        $message = 'Product not available.';
    } elseif ((int)$product['stock_qty'] <= 0) {
        $message = 'Out of stock.';
    } elseif ($qty > (int)$product['stock_qty']) {
        $message = 'Requested quantity exceeds available stock.';
    } else {
        $linePrice = (float)$product['price'];
        $total = $linePrice * $qty;

        // Create order
        $ostmt = $conn->prepare('INSERT INTO orders (customer_id, status, total) VALUES (?, "pending", ?)');
        $ostmt->bind_param('id', $customerId, $total);
        if ($ostmt->execute()) {
            $orderId = $conn->insert_id;
            $iStmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            $iStmt->bind_param('iiid', $orderId, $pid, $qty, $linePrice);
            $iStmt->execute();
            $iStmt->close();
            $message = 'Order requested. Status: pending';
        } else {
            $message = 'Could not create order.';
        }
        $ostmt->close();
    }
}

// Fetch approved products
$products = [];
$productSql = "SELECT p.id, p.name, p.price, p.stock_qty, p.created_at, c.name AS category_name, s.name AS supplier_name
               FROM products p
               LEFT JOIN categories c ON p.category_id = c.id
               JOIN supplier s ON p.supplier_id = s.sid
               WHERE p.status = 'approved' AND p.stock_qty > 0
               ORDER BY p.created_at DESC";
if ($res = $conn->query($productSql)) {
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
    $res->free();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7fb; margin:0; }
        header { background: #2d2f83; color: #fff; padding: 16px 24px; display:flex; justify-content: space-between; align-items:center; }
        .top-links a { color:#fff; margin-left:16px; text-decoration:none; font-weight:600; }
        .section { padding: 20px; }
        h3 { margin: 12px 0; }
        .message { margin-bottom: 12px; padding: 10px 12px; border-radius: 6px; background: #e8f0fe; color: #1a54b3; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        th, td { padding: 12px 14px; border-bottom: 1px solid #ececf1; text-align: left; }
        th { background: #f2f3f8; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px; color: #555; }
        tr:last-child td { border-bottom: none; }
        form.inline { display:inline; }
        input.qty { width: 70px; padding: 6px; }
        button.action { padding: 6px 10px; margin-left: 4px; border: none; border-radius: 4px; cursor: pointer; background: #2d2f83; color: #fff; }
    </style>
</head>
<body>
    <header>
        <div>
            <h2 style="margin:0;">Browse Products</h2>
            <small>Request an order (pending)</small>
        </div>
        <div class="top-links">
            <a href="details.php">Profile</a>
            <a href="orders.php">Order History</a>
            <a href="login.html">Logout</a>
        </div>
    </header>

    <div class="section">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="7">No products available.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['id']); ?></td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo htmlspecialchars($p['category_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($p['supplier_name']); ?></td>
                            <td><?php echo number_format((float)$p['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($p['stock_qty']); ?></td>
                            <td>
                                <form class="inline" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                    <input class="qty" type="number" min="1" max="<?php echo (int)$p['stock_qty']; ?>" name="qty" value="1">
                                    <button class="action" type="submit">Request</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
