<?php
// Expects $list to be set; falls back to pending by default
if (!isset($list)) {
    $list = $pendingProducts;
}
?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Supplier</th>
            <th>Category</th>
            <th>Status</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="9">No items.</td></tr>
        <?php else: ?>
            <?php foreach ($list as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['id']); ?></td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td><?php echo htmlspecialchars($p['supplier_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['category_name'] ?? ''); ?></td>
                    <td><span class="status <?php echo htmlspecialchars($p['status']); ?>"><?php echo htmlspecialchars($p['status']); ?></span></td>
                    <td><?php echo htmlspecialchars($p['stock_qty']); ?></td>
                    <td><?php echo number_format((float)$p['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                    <td>
                        <form class="inline" method="post">
                            <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button class="action primary" type="submit">Approve</button>
                        </form>
                        <form class="inline" method="post">
                            <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <button class="action danger" type="submit">Reject</button>
                        </form>
                        <form class="inline" method="post" style="margin-top:6px;">
                            <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                            <input type="hidden" name="action" value="update">
                            <input class="small" type="number" min="0" name="stock_qty" value="<?php echo htmlspecialchars($p['stock_qty']); ?>" title="Stock">
                            <input class="small" type="number" step="0.01" min="0" name="price" value="<?php echo htmlspecialchars($p['price']); ?>" title="Price">
                            <button class="action neutral" type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
