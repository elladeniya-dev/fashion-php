<?php
session_start();
if (!isset($_SESSION['sid'])) {
    header('Location: login.html');
    exit();
}

require_once __DIR__ . '/../../../config/database.php';

$supplierId = (int) $_SESSION['sid'];
$message = '';

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
    $price = trim($_POST['price'] ?? '0');
    $stock = (int) ($_POST['stock_qty'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || $price === '' || $stock < 0) {
        $message = 'Name, price, and stock are required.';
    } else {
        $status = 'pending';
        $stmt = $conn->prepare("INSERT INTO products (supplier_id, category_id, name, description, price, stock_qty, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iissdis', $supplierId, $categoryId, $name, $description, $price, $stock, $status);
        if ($stmt->execute()) {
            $message = 'Product submitted for approval.';
        } else {
            $message = 'Error creating product: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch categories
$categories = [];
if ($res = $conn->query("SELECT id, name FROM categories ORDER BY name")) {
    while ($row = $res->fetch_assoc()) {
        $categories[] = $row;
    }
    $res->free();
}

// Fetch supplier products
$products = [];
$productSql = "SELECT p.id, p.name, p.status, p.stock_qty, p.price, p.created_at, c.name AS category_name
               FROM products p
               LEFT JOIN categories c ON p.category_id = c.id
               WHERE p.supplier_id = $supplierId
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
    <title>Supplier Products</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7fb; margin: 0; }
        header { background: #2d2f83; color: #fff; padding: 16px 24px; display:flex; justify-content: space-between; align-items:center; }
        .top-links a { color: #fff; margin-left: 16px; text-decoration: none; font-weight: 600; }
        .layout { display: grid; grid-template-columns: 340px 1fr; gap: 20px; padding: 20px; }
        .card { background: #fff; border-radius: 8px; padding: 16px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        label { display:block; margin: 10px 0 4px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #dcdce4; border-radius: 6px; font-size: 14px; }
        textarea { resize: vertical; min-height: 80px; }
        button { margin-top: 12px; width: 100%; padding: 12px; background: #2d2f83; color: #fff; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        th, td { padding: 12px 14px; border-bottom: 1px solid #ececf1; text-align: left; }
        th { background: #f2f3f8; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px; color: #555; }
        tr:last-child td { border-bottom: none; }
        .status { padding: 4px 8px; border-radius: 6px; font-size: 12px; display: inline-block; }
        .status.pending { background: #fff6e5; color: #b36b00; }
        .status.approved { background: #e6f5ef; color: #0f7a43; }
        .status.rejected { background: #fdecea; color: #c53030; }
        .status.low { background: #ffe6e6; color: #b11a1a; }
        .message { margin-bottom: 12px; padding: 10px 12px; border-radius: 6px; background: #e8f0fe; color: #1a54b3; }
    </style>
</head>
<body>
    <header>
        <div>
            <h2 style="margin:0;">Supplier Products</h2>
            <small>Submit items for approval</small>
        </div>
        <div class="top-links">
            <a href="details.php">Profile</a>
            <a href="../admin/login.html">Logout</a>
        </div>
    </header>

    <div class="layout">
        <div class="card">
            <h3>Add Product</h3>
            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <form method="post">
                <label>Name</label>
                <input type="text" name="name" required>

                <label>Category</label>
                <select name="category_id">
                    <option value="">Select category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Price</label>
                <input type="number" step="0.01" min="0" name="price" required>

                <label>Stock Qty</label>
                <input type="number" min="0" name="stock_qty" required>

                <label>Description</label>
                <textarea name="description" placeholder="Short description"></textarea>

                <button type="submit">Submit for Approval</button>
            </form>
        </div>

        <div>
            <div class="card" style="margin-bottom:12px;">
                <h3>Your Products</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="7">No products yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <?php $low = ((int)$p['stock_qty'] < 10) ? ' low' : ''; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['id']); ?></td>
                                <td><?php echo htmlspecialchars($p['name']); ?></td>
                                <td><?php echo htmlspecialchars($p['category_name'] ?? ''); ?></td>
                                <td><span class="status <?php echo htmlspecialchars($p['status']); ?>"><?php echo htmlspecialchars($p['status']); ?></span></td>
                                <td><span class="status<?php echo $low; ?>"><?php echo htmlspecialchars($p['stock_qty']); ?></span></td>
                                <td><?php echo number_format((float)$p['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
