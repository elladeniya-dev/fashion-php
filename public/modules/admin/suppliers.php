<?php
session_start();
if (!isset($_SESSION['Admin_id'])) {
    header('Location: login.html');
    exit();
}

require_once __DIR__ . '/../../../config/database.php';

$message = '';

// Handle supplier approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['supplier_id'])) {
    $supplierId = (int)$_POST['supplier_id'];
    $action = $_POST['action'];

    if (in_array($action, ['approve', 'reject'], true)) {
        $newStatus = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $conn->prepare('UPDATE supplier SET status = ? WHERE sid = ?');
        $stmt->bind_param('si', $newStatus, $supplierId);
        
        if ($stmt->execute()) {
            $message = $action === 'approve' 
                ? 'Supplier approved successfully.' 
                : 'Supplier rejected.';
        } else {
            $message = 'Error updating supplier status.';
        }
        $stmt->close();
    }
}

// Fetch all suppliers
$suppliers = [];
$sql = "SELECT sid, name, email, contact, address, status, created_at FROM supplier ORDER BY created_at DESC";
if ($res = $conn->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $suppliers[] = $row;
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
    <title>Supplier Approvals - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        header {
            background: white;
            border-radius: 15px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        header h1 {
            color: #2d2f83;
            font-size: 28px;
            font-weight: 600;
        }

        .header-links {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .header-links a {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
        }

        .header-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 1400px;
            margin: 0 auto;
        }

        .message {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 8px;
            background: #e8f0fe;
            color: #1a54b3;
            font-weight: 500;
        }

        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            flex: 1;
            min-width: 150px;
            padding: 20px;
            border-radius: 10px;
            color: white;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-card.pending {
            background: linear-gradient(135deg, #fff6e5 0%, #ffeaa7 100%);
            color: #b36b00;
        }

        .stat-card.approved {
            background: linear-gradient(135deg, #e6f5ef 0%, #a8e6cf 100%);
            color: #0f7a43;
        }

        .stat-card.rejected {
            background: linear-gradient(135deg, #fdecea 0%, #fab1a0 100%);
            color: #c53030;
        }

        .stat-card h3 {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            opacity: 0.9;
        }

        .stat-card p {
            font-size: 32px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        thead {
            background: linear-gradient(135deg, #2d2f83 0%, #1a1b4d 100%);
        }

        th {
            padding: 16px 14px;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
            font-size: 14px;
        }

        tbody tr {
            transition: background-color 0.2s ease;
        }

        tbody tr:hover {
            background-color: #f8f9ff;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-badge.pending {
            background: #fff6e5;
            color: #b36b00;
        }

        .status-badge.approved {
            background: #e6f5ef;
            color: #0f7a43;
        }

        .status-badge.rejected {
            background: #fdecea;
            color: #c53030;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-right: 6px;
        }

        .btn-approve {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 233, 123, 0.3);
        }

        .btn-reject {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            .container {
                padding: 20px;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Supplier Approvals</h1>
        <div class="header-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
            <a href="login.html">Logout</a>
        </div>
    </header>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php
        $pending = count(array_filter($suppliers, fn($s) => $s['status'] === 'pending'));
        $approved = count(array_filter($suppliers, fn($s) => $s['status'] === 'approved'));
        $rejected = count(array_filter($suppliers, fn($s) => $s['status'] === 'rejected'));
        ?>

        <div class="stats">
            <div class="stat-card pending">
                <h3>Pending</h3>
                <p><?php echo $pending; ?></p>
            </div>
            <div class="stat-card approved">
                <h3>Approved</h3>
                <p><?php echo $approved; ?></p>
            </div>
            <div class="stat-card rejected">
                <h3>Rejected</h3>
                <p><?php echo $rejected; ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">No suppliers registered yet.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['sid']); ?></td>
                            <td><?php echo htmlspecialchars($s['name']); ?></td>
                            <td><?php echo htmlspecialchars($s['email']); ?></td>
                            <td><?php echo htmlspecialchars($s['contact']); ?></td>
                            <td><?php echo htmlspecialchars($s['address']); ?></td>
                            <td>
                                <span class="status-badge <?php echo htmlspecialchars($s['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($s['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($s['created_at']); ?></td>
                            <td>
                                <?php if ($s['status'] === 'pending'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="supplier_id" value="<?php echo $s['sid']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="action-btn btn-approve">Approve</button>
                                    </form>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="supplier_id" value="<?php echo $s['sid']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="action-btn btn-reject">Reject</button>
                                    </form>
                                <?php elseif ($s['status'] === 'rejected'): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="supplier_id" value="<?php echo $s['sid']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="action-btn btn-approve">Approve</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="supplier_id" value="<?php echo $s['sid']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="action-btn btn-reject">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
