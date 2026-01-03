<?php
session_start();
if (!isset($_SESSION['Admin_id'])) {
    header('Location: login.html');
    exit();
}

require_once __DIR__ . '/../../../config/database.php';

$message = '';

// Handle actions: approve, reject, update stock
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($pid > 0 && in_array($action, ['approve', 'reject', 'update'], true)) {
        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE products SET status = 'approved' WHERE id = ?");
            $stmt->bind_param('i', $pid);
            $stmt->execute();
            $message = 'Product approved.';
            $stmt->close();
        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("UPDATE products SET status = 'rejected' WHERE id = ?");
            $stmt->bind_param('i', $pid);
            $stmt->execute();
            $message = 'Product rejected.';
            $stmt->close();
        } elseif ($action === 'update') {
            $stock = max(0, (int)($_POST['stock_qty'] ?? 0));
            $price = (float)($_POST['price'] ?? 0);
            $stmt = $conn->prepare("UPDATE products SET stock_qty = ?, price = ? WHERE id = ?");
            $stmt->bind_param('idi', $stock, $price, $pid);
            $stmt->execute();
            $message = 'Product updated.';
            $stmt->close();
        }
    }
}

// Fetch products grouped by status
function fetchProducts($conn, $status)
{
    $data = [];
    $sql = "SELECT p.id, p.name, p.status, p.stock_qty, p.price, p.created_at, s.name AS supplier_name, c.name AS category_name
            FROM products p
            JOIN supplier s ON p.supplier_id = s.sid
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = ?
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $status);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        $res->free();
    }
    $stmt->close();
    return $data;
}

$pendingProducts = fetchProducts($conn, 'pending');
$approvedProducts = fetchProducts($conn, 'approved');
$rejectedProducts = fetchProducts($conn, 'rejected');

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Products</title>
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
        .status.rejected { background: #fdecea; color: #c53030; }
        form.inline { display: inline; }
        input.small { width: 90px; padding: 6px; }
        button.action { padding: 6px 10px; margin-left: 4px; border: none; border-radius: 4px; cursor: pointer; }
        button.primary { background: #2d2f83; color: #fff; }
        button.danger { background: #c53030; color: #fff; }
        button.neutral { background: #f2f3f8; color: #333; }
    </style>
</head>
<body>
    <header>
        <div>
            <h2 style="margin:0;">Admin Products</h2>
            <small>Approve supplier submissions</small>
        </div>
        <div class="top-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="../supplier/details.php">Suppliers</a>
            <a href="../customer/details.php">Customers</a>
            <a href="login.html">Logout</a>
        </div>
    </header>

    <div class="section">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h3>Pending Products</h3>
        <?php include __DIR__ . '/partials/products-table.php'; ?>

        <h3>Approved Products</h3>
        <?php $list = $approvedProducts; include __DIR__ . '/partials/products-table.php'; ?>

        <h3>Rejected Products</h3>
        <?php $list = $rejectedProducts; include __DIR__ . '/partials/products-table.php'; ?>
    </div>
</body>
</html>
