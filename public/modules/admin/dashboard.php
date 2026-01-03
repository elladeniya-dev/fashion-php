<?php
session_start();
if (!isset($_SESSION['Admin_id'])) {
    header('Location: login.html');
    exit();
}

require_once __DIR__ . '/../../../config/database.php';

// Aggregate counts
$counts = [
    'customers' => 0,
    'suppliers' => 0,
    'products' => 0,
    'pending_orders' => 0,
    'pending_products' => 0,
    'low_stock' => 0,
];

$queries = [
    'customers' => "SELECT COUNT(*) AS c FROM customer",
    'suppliers' => "SELECT COUNT(*) AS c FROM supplier",
    'products' => "SELECT COUNT(*) AS c FROM products",
    'pending_orders' => "SELECT COUNT(*) AS c FROM orders WHERE status = 'pending'",
    'pending_products' => "SELECT COUNT(*) AS c FROM products WHERE status = 'pending'",
    'low_stock' => "SELECT COUNT(*) AS c FROM products WHERE stock_qty < 10",
];

foreach ($queries as $key => $sql) {
    if ($res = $conn->query($sql)) {
        $row = $res->fetch_assoc();
        $counts[$key] = (int) ($row['c'] ?? 0);
        $res->free();
    }
}

// Recent orders
$recentOrders = [];
$orderSql = "SELECT o.id, o.status, o.total, o.created_at, c.name AS customer_name
            FROM orders o
            JOIN customer c ON o.customer_id = c.cid
            ORDER BY o.created_at DESC
            LIMIT 5";
if ($res = $conn->query($orderSql)) {
    while ($row = $res->fetch_assoc()) {
        $recentOrders[] = $row;
    }
    $res->free();
}

// Recent products
$recentProducts = [];
$productSql = "SELECT p.id, p.name, p.status, p.stock_qty, p.created_at, s.name AS supplier_name
               FROM products p
               JOIN supplier s ON p.supplier_id = s.sid
               ORDER BY p.created_at DESC
               LIMIT 5";
if ($res = $conn->query($productSql)) {
    while ($row = $res->fetch_assoc()) {
        $recentProducts[] = $row;
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7fb; margin: 0; }
        header { background: #2d2f83; color: #fff; padding: 16px 24px; display:flex; justify-content: space-between; align-items:center; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; padding: 24px; }
        .card { background: #fff; border-radius: 8px; padding: 16px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .card h3 { margin: 0 0 8px; font-size: 14px; color: #555; text-transform: uppercase; letter-spacing: 0.5px; }
        .card .value { font-size: 28px; font-weight: 700; color: #2d2f83; }
        section { padding: 0 24px 24px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        th, td { padding: 12px 14px; border-bottom: 1px solid #ececf1; text-align: left; }
        th { background: #f2f3f8; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px; color: #555; }
        tr:last-child td { border-bottom: none; }
        .status { padding: 4px 8px; border-radius: 6px; font-size: 12px; display: inline-block; }
        .status.pending { background: #fff6e5; color: #b36b00; }
        .status.approved { background: #e6f5ef; color: #0f7a43; }
        .status.ready { background: #e8f0fe; color: #1a54b3; }
        .status.completed { background: #e9f8ff; color: #0b7285; }
        .status.rejected { background: #fdecea; color: #c53030; }
        .status.low { background: #ffe6e6; color: #b11a1a; }
        .actions a { color: #2d2f83; text-decoration: none; font-weight: 600; }
        .top-links a { color: #fff; margin-left: 16px; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <header>
        <div>
            <h2 style="margin:0;">Admin Dashboard</h2>
            <small>Supplier–Customer Coordination System</small>
        </div>
        <div class="top-links">
            <a href="orders.php">Orders</a>
            <a href="products.php">Products</a>
            <a href="../supplier/details.php">Suppliers</a>
            <a href="../customer/details.php">Customers</a>
            <a href="login.html">Logout</a>
        </div>
    </header>

    <div class="grid">
        <div class="card"><h3>Customers</h3><div class="value"><?php echo $counts['customers']; ?></div></div>
        <div class="card"><h3>Suppliers</h3><div class="value"><?php echo $counts['suppliers']; ?></div></div>
        <div class="card"><h3>Products</h3><div class="value"><?php echo $counts['products']; ?></div></div>
        <div class="card"><h3>Pending Orders</h3><div class="value"><?php echo $counts['pending_orders']; ?></div></div>
        <div class="card"><h3>Pending Products</h3><div class="value"><?php echo $counts['pending_products']; ?></div></div>
        <div class="card"><h3>Low Stock (&lt;10)</h3><div class="value"><?php echo $counts['low_stock']; ?></div></div>
    </div>

    <section>
        <h3>Recent Orders</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="5">No orders yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><span class="status <?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                            <td><?php echo number_format($order['total'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h3>Recent Products</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Stock</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentProducts)): ?>
                    <tr><td colspan="6">No products yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentProducts as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['id']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['supplier_name']); ?></td>
                            <td><span class="status <?php echo htmlspecialchars($product['status']); ?>"><?php echo htmlspecialchars($product['status']); ?></span></td>
                            <td><?php echo htmlspecialchars($product['stock_qty']); ?></td>
                            <td><?php echo htmlspecialchars($product['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</body>
</html>
