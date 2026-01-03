<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Details - Fashion Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            color: #c53030;
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
        }

        .header-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(240, 147, 251, 0.3);
        }

        .header-links a.secondary {
            background: #f5f5f5;
            color: #333;
        }

        .header-links a.secondary:hover {
            background: #e0e0e0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 1400px;
            margin: 0 auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .table-header h2 {
            color: #c53030;
            font-size: 22px;
            font-weight: 600;
        }

        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .stat-card {
            flex: 1;
            min-width: 200px;
            padding: 20px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 10px;
            color: white;
            box-shadow: 0 3px 10px rgba(240, 147, 251, 0.2);
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
            background: linear-gradient(135deg, #c53030 0%, #8b1f1f 100%);
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
            background-color: #fff8f8;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
            margin-right: 6px;
        }

        .btn-update {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state p {
            font-size: 16px;
            color: #666;
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }

            .container {
                padding: 20px;
                border-radius: 10px;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 10px 8px;
            }

            .action-btn {
                padding: 6px 12px;
                font-size: 12px;
            }

            .stats {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../../../config/database.php'; ?>

    <header>
        <h1>Supplier Management</h1>
        <div class="header-links">
            <a href="../admin/dashboard.php">Dashboard</a>
            <a href="../customer/details.php" class="secondary">Customer Database</a>
            <a href="../admin/login.html" class="secondary">Log Out</a>
        </div>
    </header>

    <div class="container">
        <div class="table-header">
            <h2>All Suppliers</h2>
        </div>

        <?php
        $countQuery = "SELECT COUNT(*) as total FROM supplier";
        $countResult = mysqli_query($conn, $countQuery);
        $totalSuppliers = mysqli_fetch_assoc($countResult)['total'];
        ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Suppliers</h3>
                <p><?php echo $totalSuppliers; ?></p>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Supplier ID</th>
                    <th>Supplier Name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php

                $query = "SELECT * FROM supplier";
                $result = mysqli_query($conn, $query);

            
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['sid']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                        echo "<td>";
                        echo "<a href='update.html' class='action-btn btn-update'>Update</a>";
                        echo "<a href='delete.php?id=" . $row["sid"] . "' class='action-btn btn-delete' onclick='return confirm(\"Are you sure you want to delete this supplier?\")'>Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'><div class='empty-state'><p>No supplier data found.</p></div></td></tr>";
                }

                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
