<!DOCTYPE html>
<html>
<head>
    <title>Customers Details</title>
    <style>
        body {
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            background-image: url("supplier.png");
        }

        .details {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 5px;
        }

        h1 {
            text-align: center;
            color: yellow;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
            background-color: #fff;
        }

        th {
            background-color: #333;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        a {
            text-decoration: none;
            color : yellow;
        }

        button {
            background-color: #333;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        button:hover {
            background-color: #555;
        }

        .alink {
        color:yellow;
        margin: 300px;
    }

    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../../../config/database.php'; ?>

    <div class="details">
        <a href="../admin/login.html">Log Out</a>
        <a class="alink" href="../supplier/details.php">Supplier Database</a>
        <h1>Customer Details</h1>
        <table>
            <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php
                
                $query = "SELECT * FROM customer";
                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['cid'] . "</td>";
                        echo "<td>" . $row['name'] . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "<td>" . $row['address'] . "</td>";
                        echo "<td><a href='update.html'><button>UPDATE</button></a></td>";
                        echo "<td><a href='delete.php?id=" . $row["cid"] . "'><button>DELETE</button></a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No customer data found.</td></tr>";
                }

                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
