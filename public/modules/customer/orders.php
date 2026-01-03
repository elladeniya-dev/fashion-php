<?php
session_start();
if (!isset($_SESSION['sid'])) {
    header('Location: login.html');
    exit();
}

require_once __DIR__ . '/../../../config/database.php';

function autoCancelStaleOrders($conn, $days = 3) {
    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE status IN ('pending','approved') AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
    $stmt->bind_param('i', $days);
    $stmt->execute();
    $stmt->close();
}

autoCancelStaleOrders($conn, 3);
// Resolve customer id from login session
$customerLoginId = (int) $_SESSION['sid'];
$customerId = null;
$cidStmt = $conn->prepare('SELECT cid FROM customer_login_details WHERE sid = ? LIMIT 1');
$cidStmt->bind_param('i', $customerLoginId);
if ($cidStmt->execute()) {
    $cidRes = $cidStmt->get_result();
    if ($cidRow = $cidRes->fetch_assoc()) {
        $customerId = (int)$cidRow['cid'];
        $_SESSION['cid'] = $customerId;
    }
    $cidRes->free();
}
$cidStmt->close();

if (!$customerId) {
    header('Location: login.html');
    exit();
}

// Fetch customer name
$customerName = '';
$nameStmt = $conn->prepare('SELECT name FROM customer WHERE cid = ? LIMIT 1');
$nameStmt->bind_param('i', $customerId);
if ($nameStmt->execute()) {
    $res = $nameStmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $customerName = $row['name'];
    }
    $res->free();
}
$nameStmt->close();

// Fetch orders
$orders = [];
$statusCopy = [
    'pending' => '🕒 Order received',
    'approved' => '✔ Approved by shop',
    'ready' => '📦 Ready for pickup',
    'completed' => '✅ Completed',
    'cancelled' => '✖ Cancelled',
];
$orderSql = "SELECT id, status, total, created_at FROM orders WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($orderSql);
$stmt->bind_param('i', $customerId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $orders[] = $row;
}
$res->free();
$stmt->close();

$orderIds = array_column($orders, 'id');
$orderItems = [];
if (!empty($orderIds)) {
    $idList = implode(',', array_map('intval', $orderIds));
    $itemsSql = "SELECT oi.order_id, oi.quantity, oi.price, p.name AS product_name
                 FROM order_items oi
                 JOIN products p ON oi.product_id = p.id
                 WHERE oi.order_id IN ($idList)";
    if ($res = $conn->query($itemsSql)) {
        while ($row = $res->fetch_assoc()) {
            $orderItems[(int)$row['order_id']][] = $row;
        }
        $res->free();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders - Fashion Shop</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg,#43e97b 0%,#38f9d7 100%); min-height:100vh; padding:20px; }
        header { background:#fff; border-radius:15px; padding:18px 24px; margin-bottom:20px; box-shadow:0 4px 15px rgba(0,0,0,0.1); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; }
        header h1 { color:#0f7a43; font-size:24px; font-weight:700; }
        header small { color:#555; }
        .nav-links { display:flex; gap:12px; flex-wrap:wrap; }
        .nav-links a { padding:10px 16px; background:#f5f5f5; color:#0f7a43; text-decoration:none; border-radius:8px; font-weight:600; transition: all .2s ease; }
        .nav-links a:hover { background:#e0e0e0; }
        .card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,0.08); margin-bottom:18px; }
        .order-header { display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px; align-items:center; margin-bottom:10px; }
        .order-id { font-weight:700; color:#0f7a43; }
        .status { padding:6px 12px; border-radius:20px; font-size:12px; font-weight:700; text-transform:capitalize; }
        .status.pending { background:#fff6e5; color:#b36b00; }
        .status.approved { background:#e6f5ef; color:#0f7a43; }
        .status.ready { background:#e8f0fe; color:#1a54b3; }
        .status.completed { background:#e9f8ff; color:#0b7285; }
        .status.cancelled { background:#fdecea; color:#c53030; }
        .items { margin-top:8px; }
        .items ul { list-style:none; padding-left:0; }
        .items li { padding:6px 0; border-bottom:1px solid #f0f0f0; display:flex; justify-content:space-between; color:#444; font-size:14px; }
        .items li:last-child { border-bottom:none; }
        .total { margin-top:10px; font-weight:700; color:#0a5c32; }
        .empty { text-align:center; padding:60px 20px; color:#555; background:#fff; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.08); }
        @media (max-width: 600px) { header { flex-direction:column; align-items:flex-start; } }
    </style>
</head>
<body>
    <header>
        <div>
            <h1>Your Orders</h1>
            <small>Welcome, <?php echo htmlspecialchars($customerName); ?></small>
        </div>
        <div class="nav-links">
            <a href="orders.php">Order History</a>
            <a href="products.php">Browse Products</a>
            <a href="profile.php">Profile</a>
            <a href="login.html">Logout</a>
        </div>
    </header>

    <?php if (empty($orders)): ?>
        <div class="empty">You haven't placed any orders yet.</div>
    <?php else: ?>
        <?php foreach ($orders as $o): ?>
            <div class="card">
                <div class="order-header">
                    <div class="order-id">Order #<?php echo htmlspecialchars($o['id']); ?></div>
                    <?php $statusText = $statusCopy[$o['status']] ?? $o['status']; ?>
                    <span class="status <?php echo htmlspecialchars($o['status']); ?>"><?php echo htmlspecialchars($statusText); ?></span>
                </div>
                <div class="items">
                    <?php $items = $orderItems[$o['id']] ?? []; ?>
                    <?php if (empty($items)): ?>
                        <div style="color:#777;">No items found.</div>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($items as $it): ?>
                                <li>
                                    <span><?php echo htmlspecialchars($it['product_name']); ?> × <?php echo (int)$it['quantity']; ?></span>
                                    <span>$<?php echo number_format((float)$it['price'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="total">Total: $<?php echo number_format((float)$o['total'], 2); ?></div>
                <div style="margin-top:8px; color:#666; font-size:13px;">Placed: <?php echo htmlspecialchars($o['created_at']); ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
