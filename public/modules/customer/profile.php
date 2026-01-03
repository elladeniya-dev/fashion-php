<?php
session_start();
if (!isset($_SESSION['sid'])) {
    header('Location: login.html');
    exit();
}

include_once __DIR__ . '/../../../config/database.php';

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

$query = "SELECT name, email, contact, address, created_at FROM customer WHERE cid = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $customerId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f7f7fb; padding:20px; }
        header { background:#2d2f83; color:#fff; padding:16px 24px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; box-shadow:0 4px 12px rgba(0,0,0,0.08); }
        .top-links a { color:#fff; margin-left:14px; text-decoration:none; font-weight:600; }
        .card { margin-top:16px; background:#fff; border-radius:12px; padding:20px; box-shadow:0 4px 12px rgba(0,0,0,0.08); max-width:620px; }
        .card h2 { margin-bottom:12px; color:#2d2f83; }
        .field { margin:8px 0; color:#333; font-size:15px; }
        .field strong { display:inline-block; width:100px; color:#555; }
        .meta { color:#777; font-size:13px; margin-top:12px; }
    </style>
</head>
<body>
    <header>
        <div>
            <h2 style="margin:0;">Your Profile</h2>
            <small>Manage your info and orders</small>
        </div>
        <div class="top-links">
            <a href="products.php">Products</a>
            <a href="orders.php">Order History</a>
            <a href="login.html">Logout</a>
        </div>
    </header>

    <div class="card">
        <?php if ($customer): ?>
            <h2>Hello, <?php echo htmlspecialchars($customer['name']); ?></h2>
            <div class="field"><strong>Email</strong><?php echo htmlspecialchars($customer['email']); ?></div>
            <div class="field"><strong>Contact</strong><?php echo htmlspecialchars($customer['contact']); ?></div>
            <div class="field"><strong>Address</strong><?php echo htmlspecialchars($customer['address']); ?></div>
            <div class="meta">Joined: <?php echo htmlspecialchars($customer['created_at']); ?></div>
        <?php else: ?>
            <p>Customer not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
