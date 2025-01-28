<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login_user.html');
    exit();
}

$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders - THE GARDEN</title>
    <link rel="stylesheet" href="stylepro.css">
    <style>
        /* Container for the orders */
        #orders-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2a7f2a;
            color: white;
            font-size: 18px;
        }

        tr:hover {
            background-color: #eaf3e1;
        }

        /* No orders message */
        .no-orders {
            text-align: center;
            padding: 20px;
            font-size: 18px;
            color: #555;
        }

        /* Button styling */
        .cancel-button {
            padding: 10px 20px;
            background-color: #d9534f;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }

        .cancel-button:hover {
            background-color: #c9302c;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            #orders-container {
                padding: 10px;
            }

            th,
            td {
                padding: 10px;
                font-size: 14px;
            }

            .cancel-button {
                font-size: 12px;
                padding: 8px 15px;
            }
        }

        @media screen and (max-width: 480px) {
            table {
                font-size: 14px;
            }

            th,
            td {
                padding: 8px;
            }

            .cancel-button {
                font-size: 12px;
                padding: 6px 12px;
            }
        }
    </style>
</head>

<body>
    <header>
        <h1>Your Orders</h1>
    </header>

    <nav id="navbar">
        <a href="1.html">Home</a>
        <a href="products.html">Products</a>
        <a href="cart.html">Cart</a>
        <a href="contact.html">Contact</a>
    </nav>

    <section id="orders-container">
        <?php
        // Fetch orders for the logged-in user
        $ordersQuery = $conn->prepare("SELECT order_id, order_date, total_price, payment_method, address, phone 
                                       FROM orders 
                                       WHERE user_id = ? 
                                       ORDER BY order_date DESC");
        $ordersQuery->bind_param("i", $userId);
        $ordersQuery->execute();
        $ordersResult = $ordersQuery->get_result();

        if ($ordersResult->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Shipping Address</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
                <?php while ($order = $ordersResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                        <td><?php echo htmlspecialchars($order['address']); ?></td>
                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                        <td>
                            <form action="cancel_order.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" class="cancel-button">Cancel Order</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <div class="no-orders">No orders found.</div>
        <?php endif; ?>
    </section>
</body>

</html>
