<?php
session_start();
if (!isset($_SESSION['Admin_id'])) {
    header('Location: login.html');
    exit();
}

require_once __DIR__ . '/../../../config/database.php';

$message = '';

// Helper to fetch order items
function getOrderItems($conn, $orderId) {
    $items = [];
    $stmt = $conn->prepare('SELECT oi.product_id, oi.quantity, oi.price, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
    $stmt->bind_param('i', $orderId);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $items[] = $row;
        }
        $res->free();
    }
    $stmt->close();
    return $items;
}

// Action handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($orderId > 0 && in_array($action, ['approve', 'ready', 'completed', 'cancel'], true)) {
        $allowedTransitions = [
            'approve' => ['pending'],
            'ready' => ['approved'],
            'completed' => ['ready'],
            'cancel' => ['pending', 'approved', 'ready']
        ];

        // Fetch current status
        $stmt = $conn->prepare('SELECT status FROM orders WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $res = $stmt->get_result();
        $order = $res->fetch_assoc();
        $res->free();
        $stmt->close();

        if ($order) {
            $currentStatus = $order['status'];

            if (!in_array($currentStatus, $allowedTransitions[$action], true)) {
                $message = 'Action not allowed for current status.';
            } elseif ($action === 'approve') {
                // Ensure stock availability and deduct within a transaction
                $conn->begin_transaction();
                $items = getOrderItems($conn, $orderId);
                $insufficient = [];

                $stockStmt = $conn->prepare('SELECT stock_qty FROM products WHERE id = ? FOR UPDATE');
                foreach ($items as $it) {
                    $pid = (int)$it['product_id'];
                    $qty = (int)$it['quantity'];
                    $stockStmt->bind_param('i', $pid);
                    $stockStmt->execute();
                    $stockRes = $stockStmt->get_result();
                    if ($stockRes && $row = $stockRes->fetch_assoc()) {
                        if ((int)$row['stock_qty'] < $qty) {
                            $insufficient[] = $it['name'];
                        }
                    }
                    if ($stockRes) { $stockRes->free(); }
                }

                if (!empty($insufficient)) {
                    $conn->rollback();
                    $message = 'Insufficient stock for: ' . implode(', ', $insufficient);
                } else {
                    $deductStmt = $conn->prepare('UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?');
                    foreach ($items as $it) {
                        $pid = (int)$it['product_id'];
                        $qty = (int)$it['quantity'];
                        $deductStmt->bind_param('ii', $qty, $pid);
                        $deductStmt->execute();
                    }
                    $deductStmt->close();

                    $up = $conn->prepare("UPDATE orders SET status = 'approved' WHERE id = ?");
                    $up->bind_param('i', $orderId);
                    $up->execute();
                    $up->close();

                    $conn->commit();
                    $message = 'Order approved and stock reserved.';
                }
                $stockStmt->close();
            } elseif ($action === 'ready') {
                $up = $conn->prepare("UPDATE orders SET status = 'ready' WHERE id = ?");
                $up->bind_param('i', $orderId);
                $up->execute();
                $up->close();
                $message = 'Order marked ready.';
            } elseif ($action === 'completed') {
                $up = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
                $up->bind_param('i', $orderId);
                $up->execute();
                $up->close();
                $message = 'Order completed.';
            } elseif ($action === 'cancel') {
                $up = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
                $up->bind_param('i', $orderId);
                $up->execute();
                $up->close();
                $message = 'Order cancelled.';
            }
        }
    }
}

// Fetch orders
$orders = [];
$sql = "SELECT o.id, o.status, o.total, o.created_at, c.name AS customer_name
        FROM orders o
        JOIN customer c ON o.customer_id = c.cid
        ORDER BY o.created_at DESC";
if ($res = $conn->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }
    $res->free();
}

// Fetch items for all orders to display
$orderItemsMap = [];
if (!empty($orders)) {
    $ids = array_column($orders, 'id');
    $idList = implode(',', array_map('intval', $ids));
    $itemsSql = "SELECT oi.order_id, oi.quantity, oi.price, p.name AS product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id IN ($idList)";
    if ($res = $conn->query($itemsSql)) {
        while ($row = $res->fetch_assoc()) {
            $oid = (int)$row['order_id'];
            if (!isset($orderItemsMap[$oid])) { $orderItemsMap[$oid] = []; }
            $orderItemsMap[$oid][] = $row;
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
    <title>Admin Orders</title>
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
        .status { padding: 4px 8px; border-radius: 6px; font-size: 12px; display: inline-block; }
        .status.pending { background: #fff6e5; color: #b36b00; }
        .status.approved { background: #e6f5ef; color: #0f7a43; }
        .status.ready { background: #e8f0fe; color: #1a54b3; }
        .status.completed { background: #e9f8ff; color: #0b7285; }
        .status.cancelled { background: #fdecea; color: #c53030; }
        form.inline { display: inline; }
        button.action { padding: 6px 10px; margin-left: 4px; border: none; border-radius: 4px; cursor: pointer; }
        button.primary { background: #2d2f83; color: #fff; }
        button.info { background: #1a54b3; color: #fff; }
        button.success { background: #0f7a43; color: #fff; }
        button.danger { background: #c53030; color: #fff; }
        ul.items { margin: 6px 0 0 0; padding-left: 18px; color: #444; }
    </style>
</head>
<body>
    <header>
        <div>
            <h2 style="margin:0;">Admin Orders</h2>
            <small>Review and progress orders</small>
        </div>
        <div class="top-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
            <a href="../supplier/details.php">Suppliers</a>
            <a href="../customer/details.php">Customers</a>
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
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Items</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7">No orders yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <?php $oid = (int)$o['id']; $items = $orderItemsMap[$oid] ?? []; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($oid); ?></td>
                            <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
                            <td><span class="status <?php echo htmlspecialchars($o['status']); ?>"><?php echo htmlspecialchars($o['status']); ?></span></td>
                            <td><?php echo number_format((float)$o['total'], 2); ?></td>
                            <td>
                                <?php if (empty($items)): ?>
                                    -
                                <?php else: ?>
                                    <ul class="items">
                                        <?php foreach ($items as $it): ?>
                                            <li><?php echo htmlspecialchars($it['product_name']); ?> × <?php echo (int)$it['quantity']; ?> @ <?php echo number_format((float)$it['price'], 2); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($o['created_at']); ?></td>
                            <td>
                                <form class="inline" method="post">
                                    <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button class="action primary" type="submit">Approve</button>
                                </form>
                                <form class="inline" method="post">
                                    <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                                    <input type="hidden" name="action" value="ready">
                                    <button class="action info" type="submit">Ready</button>
                                </form>
                                <form class="inline" method="post">
                                    <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                                    <input type="hidden" name="action" value="completed">
                                    <button class="action success" type="submit">Complete</button>
                                </form>
                                <form class="inline" method="post">
                                    <input type="hidden" name="order_id" value="<?php echo $oid; ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <button class="action danger" type="submit">Cancel</button>
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
